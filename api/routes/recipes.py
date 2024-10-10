import json

from flask import Blueprint, request, redirect
from mongoengine import DoesNotExist

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
        'msg': 'Recipe created',
    }, 200


@recipes.get('/<post_id>')
def get_recipe(post_id):
    try:
        recipe = Post.objects.get(id=post_id)
    except DoesNotExist:
        return {
            'code': 404,
            'msg': "Recipe not found",
            'data': {}
        }, 404
    base_json = json.loads(recipe.to_json())
    base_json['id'] = base_json["_id"]["$oid"]
    del base_json['_id']
    return {
        'code': 200,
        'data': base_json,
        'msg': "Recipe found"
        }, 200


@recipes.patch('/<recipe_id>')
def modify_recipe(recipe_id):
    rjson = request.get_json()

    if not rjson:
        return {
            'code': 406,
            'msg': "Missing Data",
            'data': None
        }, 406

    to_remove = []

    DISALLOWED_FIELDS = ["id", "user"]

    # TODO: Skip if the user is an Admin
    for field in rjson:
        if field in DISALLOWED_FIELDS:
            to_remove.append(field)

    if len(to_remove) > 0:
        return {
            'code': 403,
            'msg': "Attempted to modify: {}".format(to_remove),
        }, 403

    try:
        recipe = Post.objects.get(id=recipe_id)
    except DoesNotExist:
        return {
            'code': 404,
            'msg': "Recipe not found",
            'data': {}
        }, 404

    updated = recipe.update(**rjson)
    recipe.reload()
    if updated is 0:
        return {
            'code': 500,
            'msg': "Internal Error. Recipe update unsuccessful.",
            'data': {}
        }, 500

    base_json = json.loads(recipe.to_json())
    base_json['id'] = base_json["_id"]["$oid"]
    del base_json['_id']
    return {
        'code': 200,
        'data': base_json,
        'msg': "Recipe Updated"
    }, 200


@recipes.route("/testing", methods=["GET", "POST"])
def upload_file():
    if request.method == "POST":
        print(request.form.to_dict().items())
        if request.form.get("id"):
            new_data = request.form.to_dict()
            rid = request.form.get("id")
            del new_data["id"]
            recipe = Post.objects.get(id=rid)
            recipe.update(**request.form.to_dict())
            return redirect(request.url)

        return redirect(request.url)

    recipe_id = request.args.get("id", None)
    recipe = {}
    if recipe_id:
        try:
            recipe = Post.objects.get(id=recipe_id)
            recipe = json.loads(recipe.to_json())
            recipe['id'] = recipe["_id"]["$oid"]
            del recipe['_id']
        except DoesNotExist:
            pass
    return f"""
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Tag Testing</title>
        </head>
        <body>
          <h1>Create Recipe</h1>
          <form method=post enctype=multipart/form-data>
            ID <input type=text name=id value="{recipe.get('id', '')}" readonly><br>
            User <input type=text name=label value="{recipe.get('user', "")}"><br>
            Title <input type=text name=emoji value="{recipe.get('title', "")}"><br>
            Description <input type=text name=color value="{recipe.get('description', "")}"><br>
            Tags <input type=text name=color value="{recipe.get('tags', "")}"><br>
            Steps <input type=text name=color value="{recipe.get('steps', "")}"><br>
            Ingredients <input type=text name=color value="{recipe.get('ingredients', "")}"><br>
            Time to Cook <input type=text name=color value="{recipe.get('time_to_cook', "")}"><br>
            Time to Prepare <input type=text name=color value="{recipe.get('time_to_prepare', "")}"><br>
            Skill Level <input type=text name=color value="{recipe.get('skill_level', "")}"><br>
            Reviews <input type=text name=color value="{recipe.get('reviews', "")}"><br>
            <input type=submit value=Create/Edit>
          </form>
        </body>
        </html>
        """