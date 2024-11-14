import json
from dataclasses import asdict

import requests
from flask import Blueprint, request, redirect
from mongoengine import DoesNotExist

from models.history import History
from models.post import Post, Review
from models.user import Users
from util.api import make_spoonacular_api_call, spoonacular_api_to_internal
from util.auth import authed, can_do_admin_requests

recipes = Blueprint('recipes', __name__)


@recipes.post('/')
@authed
def post_recipe(user):
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
    if post_id.startswith('sp_'):
        rcode, recipe = make_spoonacular_api_call(f"recipes/{post_id[3:]}/information", "get")

        return {'code': 200, 'data': spoonacular_api_to_internal(recipe), 'msg': 'recipe found!'}, 200

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
    base_json['reviews'] = len(base_json['reviews'])
    del base_json['_id']
    return {
        'code': 200,
        'data': base_json,
        'msg': "Recipe found"
        }, 200


@recipes.patch('/<recipe_id>')
@authed
def modify_recipe(user, recipe_id):
    rjson = request.get_json()

    if not rjson:
        return {
            'code': 406,
            'msg': "Missing Data",
            'data': None
        }, 406

    to_remove = []

    DISALLOWED_FIELDS = ["id", "user"]

    if not can_do_admin_requests(user):
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
    if updated == 0:
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


@recipes.get("/random")
@authed
def random_recipe(user):
    random = Post.objects.aggregate([{"$sample": {"size": 1}}])
    base_recipe = next(random, None)

    if base_recipe:
        random_recipe = Post._from_son(base_recipe)
        base_json = json.loads(random_recipe.to_json())
        base_json['id'] = base_json["_id"]["$oid"]
        del base_json['_id']

        return {'code': 200, 'data': base_json, 'msg': "Random Recipe"}, 200
    else:
        return "No recipe found", 404


@recipes.get('/<recipe_id>/reviews')
@authed
def get_recipe_reviews(user, recipe_id):
    try:
        recipe = Post.objects.get(id=recipe_id)
    except DoesNotExist:
        return {
            'code': 404,
            'msg': "Recipe not found",
            'data': {}
        }, 404
    
    base_json = json.loads(recipe.to_json())
    base_json['id'] = base_json["_id"]["$oid"]
    reviews = base_json['reviews']
    new_reviews = []
    for review in reviews:
        user = Users.get_or_none(id=review['user'])
        if not user:
            review['user'] = {
                "username": f"deleted_user_{review['user']}",
                "name": "Deleted User",
                "avatar": "default.png"
            }
        else:
            review['user'] = user.to_dict()

        new_reviews.append(review)

    return {'code': 200, 'data': new_reviews, 'msg': ''}, 200


@recipes.post("/<recipe_id>/reviews")
@authed
def review_recipe(user, recipe_id):
    try:
        recipe = Post.objects.get(id=recipe_id)
    except DoesNotExist:
        return {
            'code': 404,
            'msg': "Recipe not found",
            'data': {}
        }, 404

    rjson = request.get_json()
    if not rjson:
        return {
            'code': 406,
            'msg': "Missing Data",
            'data': {}
        }, 406

    re = Review(**rjson, user=user.id)

    updooted = recipe.update(add_to_set__reviews=asdict(re))
    recipe.reload()
    if updooted != 0:
        return {
            'code': 200,
            'msg': "Review Added",
            'data': {}
        }, 200
    else:
        return {
            'code': 400,
            'msg': "Review Not Added",
            'data': {}
        }, 400


@recipes.route("/recommended")
@authed
def recommended_recipe(user):
    pass


@recipes.route("sp-random")
@authed
def spoonacular_recipe(user):
    rcode, data = make_spoonacular_api_call("recipes/random", "get")
    formatted = spoonacular_api_to_internal(data['recipes'][0])
    return {
        "og": data,
        "formatted": formatted
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
