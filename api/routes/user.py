from flask import Blueprint

from models.user import Users
from util.auth import authed

user_api = Blueprint('user_api', __name__)


@user_api.get("/self")
@authed
def get_own_user(user):
    return {'code': 200, 'data': user.to_dict(), 'msg': None}, 200


@user_api.get("/<uid>")
@authed
def get_user_by_id(user, uid):
    to_get = Users.get_or_none(id=uid)
    return {
        'code': 200 if to_get else 404,
        'data': to_get.to_dict(),
        'msg': "User found" if to_get else "User not found"
    }, 200 if user else 404


@user_api.delete("/<uid>")
@authed
def delete_user(user, uid):
    if 'ADMIN' not in user.account_flags:
        return {
            'code': 401,
            'data': None,
            'msg': 'Forbidden'
        }, 401
    to_remove = Users.get_or_none(id=uid)
    if to_remove:
        Users.delete_by_id(id=to_remove.id)
        return {
            'code': 200,
            'data': None,
            'msg': "User removed successfully"
        }, 200
    else:
        return {
            'code': 404,
            'data': None,
            'msg': "User not found"
        }, 404
