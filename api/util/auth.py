from functools import wraps
import jwt
from flask import request, abort
from flask import current_app
from werkzeug.security import generate_password_hash


def encrypt_password(password):
    return generate_password_hash(password)


def authed(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = None
        if "Authorization" in request.headers:
            token = request.headers["Authorization"].split(" ")[1]
        if not token:
            return {
                "message": "Missing Authorization Token",
                "data": None,
                "error": "Unauthorized"
            }, 401
        try:
            data = jwt.decode(token, current_app.config["SECRET_KEY"], algorithms=["HS256"])
            current_user = None

            if current_user is None:
                return {
                    "message": "Invalid Authentication Token",
                    "data": None,
                    "error": "Unauthorized"
                }, 401
            if not current_user["active"]:
                abort(403)
        except Exception as e:
            return {
                "message": "Something went wrong",
                "data": None,
                "error": str(e)
            }, 500

        return f(current_user, *args, **kwargs)

    return decorated