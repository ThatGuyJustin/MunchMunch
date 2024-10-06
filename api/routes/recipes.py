import json

from flask import Blueprint, request

from models.post import Post
from util.auth import authed

recipes = Blueprint('recipes', __name__)


@recipes.post('/')
# @authed
def post_recipe():
    _FIELDS = ["user", "title", "description", "steps", "ingredients", "time_to_cook", "time_to_prepare", "skill_level"]
    missing_fields = []

    recipe = request.get_json()

    for field in _FIELDS:
        if field not in recipe:
            missing_fields.append(field)

    if len(missing_fields) > 0:
        return {
            'code': 406,
            'msg': "Missing fields: {}".format(missing_fields),
            'data': {}
        }, 406

    new_post = Post(**recipe).save()

    base_json = json.loads(new_post.to_json())
    base_json['id'] = base_json["_id"]["$oid"]
    del base_json['_id']
    return {
        'code': 200,
        'data': base_json,
    }, 200


@recipes.get('/<post_id>')
def get_recipe(post_id):
    recipe = Post.objects.get(id=post_id)
    base_json = json.loads(recipe.to_json())
    print(base_json)
    base_json['id'] = base_json["_id"]["$oid"]
    del base_json['_id']
    if not recipe:
        return {
            'code': 404,
            'msg': "Recipe not found",
        }, 404
    else:
        return {
            'code': 200,
            'data': base_json,
        }, 200
