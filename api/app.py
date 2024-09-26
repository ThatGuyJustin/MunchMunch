import os

import jwt
from flask import Flask, Blueprint, request
from peewee import fn

from db import init_db
from models.user import Users
from util.auth import encrypt_password, authed
from util.validation import validate_username, validate_email

app = Flask(__name__)
JWT_SECRET = os.environ.get('JWT_SECRET_KEY') or "SomethingSuperSecure?"
app.config['JWT_SECRET_KEY'] = JWT_SECRET

api = Blueprint('api', __name__)

with app.app_context():
    app.pg_db = init_db()


@api.get('/hello-world')
def hello_world():
    return {'code': 200, "msg": "Hello World!"}, 200


@api.get("/user_list")
def user_list():
    try:
        user_count = list(Users.select(fn.COUNT(Users.id)))
        print(user_count)
        return {'code': 200, "msg": False}, 200
    except Exception as e:
        return {'code': 500, "msg": e.with_traceback()}, 500


@api.get("/user/<uid>")
@authed
def get_user_by_id(uid):
    user = Users.get_or_none(id=uid)
    return {
        'code': 200 if user else 404,
        'data': user.to_dict(),
        'msg': "User found" if user else "User not found"
    }, 200 if user else 404


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

    return {"code": 200, "msg": "Registration validated :)", "user": user.to_dict()}, 200


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
    app.run(host="0.0.0.0", debug=True)
