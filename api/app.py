import hashlib
import mimetypes
import os
from io import BytesIO

import jwt
from flask import Flask, Blueprint, request, current_app, redirect, send_file
from minio import Minio
from peewee import fn
from werkzeug.utils import secure_filename

from db import init_db, postgres_db
from models.user import Users
from routes.media import media
from routes.user import user_api
from util.auth import encrypt_password, authed
from util.files import allowed_file, upload_object
from util.validation import validate_username, validate_email

app = Flask(__name__)
JWT_SECRET = os.environ.get('JWT_SECRET_KEY') or "SomethingSuperSecure?"
app.config['JWT_SECRET_KEY'] = JWT_SECRET
app.config['REQUIRE_CONFIRMATION'] = bool(os.environ.get('REQUIRE_CONFIRMATION')) or False
app.config['SECURE_PASSSWORD_SALT'] = os.environ.get('SECURE_PASSSWORD_SALT') or "SomethingSuperSecure?"
app.config["S3_USERNAME"] = os.environ.get("S3_USERNAME")
app.config["S3_PASSWORD"] = os.environ.get("S3_PASSWORD")
app.config["S3_BUCKET"] = os.environ.get("S3_BUCKET")
app.config["S3_HOST"] = os.environ.get("S3_HOST")

api = Blueprint('api', __name__)

with app.app_context():
    app.pg_db = init_db()
    # Ensure MinIO is online, and bucket is found!
    s3_client = Minio(app.config['S3_HOST'], app.config['S3_USERNAME'], app.config['S3_PASSWORD'], secure=False)
    if s3_client.bucket_exists(app.config['S3_BUCKET']):
        app.s3_client = s3_client
    else:
        s3_client.make_bucket(app.config['S3_BUCKET'])
        app.s3_client = s3_client


@api.get('/hello-world')
def hello_world():
    return {'code': 200, "msg": "Hello World!"}, 200


@api.get('reset_backend')
def reset_backend():
    to_reset = []
    if request.args.get("main_database"):
        to_reset.append("main_database")
    if request.args.get("user_table"):
        to_reset.append("user_table")

    for part in to_reset:
        if part == "user_table" or part == "main_database":
            postgres_db.drop_tables(Users)
            postgres_db.create_tables(Users)
            return "👌", 200


@api.get('/media/test/<hash_id>')
def test_get_media(hash_id):
    print(hash_id)
    if not hash_id:
        return "Media Not Found", 404
    else:
        try:
            req = app.s3_client.get_object(app.config['S3_BUCKET'], "yoshi/" + hash_id)
            print(dict(req.headers).items())
            print(dir(req))
            data = BytesIO(req.read())
            req.close()
            return send_file(data, as_attachment=False, mimetype=mimetypes.guess_type(hash_id)[0], download_name=hash_id)
        except:
            return "Media Not Found", 404


@api.route("/s3-test", methods=["GET", "POST"])
def upload_file():
    if request.method == "POST":
        # check if the post request has the file part
        if "file" not in request.files:
            return redirect(request.url)
        file = request.files["file"]
        # If the user does not select a file, the browser submits an
        # empty file without a filename.
        if file.filename == "":
            return redirect(request.url)
        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            new_filename = hashlib.md5(file.read()).hexdigest()
            file.seek(0)
            ext = secure_filename(file.filename).split(".")[-1]
            new_filename = new_filename + "." + ext
            size = os.fstat(file.fileno()).st_size
            path = request.form["folder"]
            upload_object(new_filename, file, size, path)
            return redirect(request.url)

    return """
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>UPLOAD</title>
        </head>
        <body>
          <h1>Upload File</h1>
          <form method=post enctype=multipart/form-data>
            <input type=file name=file>
            <input type=text name=folder>
            <input type=submit value=Upload>
          </form>
        </body>
        </html>
        """


@api.post("/register")
def register():
    data = request.json

    if not data:
        return {"code": 400, "msg": "Malformed request."}, 400

    REQUIRED_FIELDS = ['username', 'password', 'email']

    for field in REQUIRED_FIELDS:
        if field not in data.keys():
            return {"code": 400, "msg": "Malformed request."}, 400

    valid_username = validate_username(data["username"])
    if not valid_username:
        return {"code": 400, "msg": "Invalid username or username is already taken."}, 400

    valid_email = validate_email(data["email"])
    if not valid_email:
        return {"code": 400, "msg": "Invalid email or email is already in use."}, 400

    # TODO: Password validation /shrug

    user = Users.create(username=data["username"], email=data["email"], password=encrypt_password(data["password"]))
    if current_app.config["REQUIRE_CONFIRMATION"]:
        user.account_flags.append("PENDING_CONFIRMATION")
        user.save()
        # Generate Validation Code



    return {"code": 200, "msg": "Registration Successful. :)", "user": user.to_dict()}, 200


@api.post("/login")
def login():
    data = request.json
    if not data:
        return {"code": 400, "msg": "Malformed request."}, 400

    if "password" not in data.keys() or "login" not in data.keys():
        return {"code": 400, "msg": "Incomplete Login."}, 403

    user = Users.login(login=data["login"], password=data["password"])

    if user:
        user_dict = user.to_dict()
        try:
            user_dict["token"] = jwt.encode({
                "user_id": user.id},
                app.config["JWT_SECRET_KEY"],
                algorithm="HS256"
            )

            return {
                "code": 200,
                "msg": "Login Successful",
                "user": user_dict
            }
        except Exception as e:
            return {
                "code": 500,
                "error": "Something went wrong",
                "message": str(e)
            }, 500

    return {
        "message": "Error fetching auth token!, invalid email or password",
        "data": None,
        "error": "Unauthorized"
    }, 404


if __name__ == '__main__':
    app.register_blueprint(api, url_prefix='/api')
    app.register_blueprint(user_api, url_prefix='/api/users')
    app.register_blueprint(media, url_prefix='/api/media')
    app.run(host="0.0.0.0", debug=True)
