from flask import Blueprint, request

from models.user import Users as User_model
from util.auth import authed

users = Blueprint('users', __name__)


@users.get("/self")
@authed
def get_own_user(user):
    return {'code': 200, 'data': user.to_dict(), 'msg': None}, 200


@users.get("/<uid>")
@authed
def get_user_by_id(user, uid):
    to_get = User_model.get_or_none(id=uid)
    return {
        'code': 200 if to_get else 404,
        'data': to_get.to_dict(),
        'msg': "User found" if to_get else "User not found"
    }, 200 if user else 404


@users.patch("/<uid>")
# @authed
def modify_user(uid):

    if not request.json:
        return {"code": 400, "msg": "Missing Data"}, 400

    to_update = User_model.get_or_none(id=uid)
    if not to_update:
        return {
            'code': 404,
            'msg': "User not found",
            'data': {}
        }

    User_model.update(**request.json).where(User_model.id == uid).execute()
    to_update = User_model.get_or_none(id=uid)
    return {'code': 200, 'data': to_update.to_dict(), 'msg': 'User Updated.'}, 200


@users.delete("/<uid>")
@authed
def delete_user(user, uid):
    if 'ADMIN' not in user.account_flags:
        return {
            'code': 403,
            'data': None,
            'msg': 'Forbidden'
        }, 403
    to_remove = User_model.get_or_none(id=uid)
    if to_remove:
        User_model.delete_by_id(id=to_remove.id)
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


@users.put("/favorites/<recipe>")
@authed
def put_favorite_recipe(user, recipe):

    if recipe in user.favorite_posts:
        return "👎", 200

    user.favorite_posts.append(recipe)
    user.save()

    return "👌", 200


@users.delete("/favorites/<recipe>")
def delete_favorite_recipe(user, recipe):

    if recipe not in user.favorite_posts:
        return "👎", 200

    user.favorite_posts.remove(recipe)
    user.save()

    return "👌", 200