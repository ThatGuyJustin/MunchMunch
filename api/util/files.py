import hashlib
from io import BytesIO

from flask import current_app
from werkzeug.utils import secure_filename

ALLOWED_EXTENSIONS = {"png", "jpg", "jpeg", "gif"}


def generate_filename(file) -> str:
    filename = secure_filename(file.filename)
    new_filename = hashlib.md5(file.read()).hexdigest()
    file.seek(0)
    ext = secure_filename(file.filename).split(".")[-1]
    new_filename = new_filename + "." + ext
    return new_filename


def upload_object(filename, data, length, path):
    s3_client = current_app.s3_client

    s3_client.put_object(current_app.config["S3_BUCKET"], f"{path}/{filename}", data, length)


def allowed_file(filename):
    return "." in filename and filename.rsplit(".", 1)[1].lower() in ALLOWED_EXTENSIONS


def get_object(path: str, object_name: str):
    try:
        req = current_app.s3_client.get_object(current_app.config['S3_BUCKET'], f"{path}/{object_name}")
        data = BytesIO(req.read())
        req.close()
        return data
    except:
        return None