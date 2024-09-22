from flask import Flask, Blueprint, request
from peewee import fn

from db import init_db
from models.user import Users
from util.validation import validate_username, validate_email

app = Flask(__name__)

api = Blueprint('api', __name__)

with app.app_context():
    app.pg_db = init_db()


@api.get('/test-connection')
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

@api.post("/register")
def register():
    body = request.json

    if not body:
        return {"code": 400, "msg": "Malformed request."}, 400

    REQUIRED_FIELDS = ['username', 'password', 'email']

    for field in REQUIRED_FIELDS:
        if field not in body.keys():
            return {"code": 400, "msg": "Malformed request."}, 400

    valid_username = validate_username(body["username"])
    if not valid_username:
        return {"code": 400, "msg": "Invalid username or username is already taken."}, 400

    valid_email = validate_email(body["email"])
    if not valid_email:
        return {"code": 400, "msg": "Invalid email or email is already in use."}, 400

    user = Users.create(username=body["username"], email=body["email"])

    return {"code": 200, "msg": "Registration validated :)", "user": user.to_dict()}, 200


if __name__ == '__main__':
    app.register_blueprint(api, url_prefix='/api')
    app.run(host="0.0.0.0", debug=True)
