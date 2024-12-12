import datetime
import json

from flask import Blueprint, request, redirect
from mongoengine import DoesNotExist

from models.history import History
from models.mealplan import MealPlan
from models.post import Post
from models.shopping import ShoppingList
from models.user import Users as User_model
from util.api import make_spoonacular_api_call, spoonacular_api_to_internal
from util.auth import authed, can_do_admin_requests

users = Blueprint('users', __name__)


@users.get("/self")
@authed
def get_own_user(user):
    return {'code': 200, 'data': user.to_dict(), 'msg': None}, 200


@users.get("/<query>")
@authed
def get_user_by_id(user, query):
    to_get = None
    if query.isdigit():
        to_get = User_model.get_or_none(id=query)
    else:
        to_get = User_model.get_or_none(username=query)
    return {
        'code': 200 if to_get else 404,
        'data': to_get.to_dict() if to_get else {},
        'msg': "User found" if to_get else "User not found"
    }, 200 if to_get else 404


@users.patch("/<uid>")
@authed
def modify_user(user, uid):

    if not request.json:
        return {"code": 400, "msg": "Missing Data"}, 400

    if not can_do_admin_requests(user) and int(uid) != int(user.id):
        return {"code": 401, "msg": "May not modify other users."}, 401

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


@users.get("/<uid>/recipes")
@authed
def get_recipes(user, uid):
    u = User_model.get_or_none(id=uid)
    if not u:
        return {"code": 404, "msg": "User not found"}, 404
    if "PRIVATE_PROFILE" in u.account_flags and not can_do_admin_requests(user) and int(uid) != int(user.id):
        return {"code": 403, "data": {}, "msg": "User's Profile Is Private."}, 403

    limit = request.args.get("limit", 50)
    offset = request.args.get("offset", None)

    query = Post.objects(user=u.id)
    query = query.limit(limit)
    if offset is not None:
        query = query.skip(offset)

    to_return = []

    for post in query:
        base_json = json.loads(post.to_json())
        base_json['id'] = base_json["_id"]["$oid"]
        formatted = post.created_at.strftime("%m.%d.%Y %H:%M")
        base_json['created_at'] = formatted
        del base_json['_id']
        to_return.append(base_json)

    return {"code": 200, "data": to_return, "msg": None}, 200


@users.get("/<uid>/favorites")
@authed
def get_user_favorites(user, uid):
    u = User_model.get_or_none(id=uid)
    if not u:
        return {"code": 404, "msg": "User not found"}, 404
    if "PRIVATE_FAVORITES" in u.account_flags and not can_do_admin_requests(user) and int(uid) != int(user.id):
        return {"code": 403, "data": {}, "msg": "User's Favorites Are Private."}, 403

    raw_recipes = {}
    to_return = []
    sp_recipes = []
    for fav in u.favorite_posts:
        if fav.startswith('sp_'):
            sp_recipes.append(fav[3:])
            to_return.append(fav)
        else:
            recipe = Post.objects(id=fav).get()
            base_json = json.loads(recipe.to_json())
            base_json['id'] = base_json["_id"]["$oid"]
            formatted = recipe.created_at.strftime("%m.%d.%Y %H:%M")
            base_json['created_at'] = formatted
            del base_json['_id']
            to_return.append(fav)
            raw_recipes[fav] = base_json

    if len(sp_recipes):
        rcode, all_recipes = make_spoonacular_api_call(f"recipes/informationBulk", "get", params={"ids": ",".join(sp_recipes)})
        if rcode != 429:
            for recipe in all_recipes:
                raw_recipes[f"sp_{recipe['id']}"] = spoonacular_api_to_internal(recipe)
        else:
            to_return = [recipe for recipe in to_return if not recipe.startswith('sp_')]

    data = {
        "favorites": to_return,
        "raw_recipes": raw_recipes,
    }

    return {"code": 200, "data": data, "msg": None}, 200


@users.post("/<uid>/history")
@authed
def post_history(user, uid):

    if not can_do_admin_requests(user) and int(uid) != int(user.id):
        return {"code": 401, "msg": "May not modify other users' history."}, 401

    rjson = request.get_json()

    new_history = History(**rjson, user=uid).save()

    return {"code": 200, "data": json.loads(new_history.to_json())}, 200


@users.get("/<uid>/history")
@authed
def get_history(user, uid):
    if not can_do_admin_requests(user) and int(uid) != int(user.id):
        return {"code": 401, "msg": "May not view other users' History."}, 401

    limit = request.args.get("limit", 50)
    offset = request.args.get("offset", None)

    query = History.objects(user=uid)
    query = query.limit(limit)
    if offset is not None:
        query = query.skip(offset)

    raw_recipes = {}
    to_return = []
    sp_recipes = []

    for history_obj in query:

        base_json = json.loads(history_obj.to_json())
        # base_json['recipe'] = base_json["recipe"]["$oid"]
        base_json['id'] = base_json["_id"]
        bformatted = history_obj.timestamp.strftime("%m.%d.%Y %H:%M")
        base_json['timestamp'] = bformatted
        del base_json['_id']
        if base_json['recipe'].startswith('sp_'):
            sp_recipes.append(base_json['recipe'][3:])
        else:
            r = Post.objects(id=base_json['recipe']).get()
            recipe_json = json.loads(r.to_json())
            recipe_json['id'] = recipe_json["_id"]["$oid"]
            formatted = r.created_at.strftime("%m.%d.%Y %H:%M")
            recipe_json['created_at'] = formatted
            del recipe_json['_id']
            raw_recipes[history_obj['recipe']] = recipe_json
        to_return.append(base_json)

    if len(sp_recipes):
        rcode, all_recipes = make_spoonacular_api_call(f"recipes/informationBulk", "get",
                                                       params={"ids": ",".join(sp_recipes)})
        if rcode != 429:
            for recipe in all_recipes:
                raw_recipes[f"sp_{recipe['id']}"] = spoonacular_api_to_internal(recipe)
        else:
            to_return = [recipe for recipe in to_return if not recipe.startswith('sp_')]

    data = {
        "history": to_return,
        "raw_recipes": raw_recipes,
    }

    return {"code": 200, "data": data, "msg": None}, 200


@users.post("/<uid>/shopping-list")
@authed
def post_shopping_list(user, uid):
    rjson = request.get_json()

    new_list = ShoppingList(**rjson, user=uid).save()

    return {"code": 200, "data": json.loads(new_list.to_json()), "msg": None}, 200


@users.get("/<uid>/shopping-list")
@authed
def get_shopping_list(user, uid):
    try:
        slist = ShoppingList.objects.get(user=user.id)
    except DoesNotExist:
        slist = ShoppingList(recipes=[], ingredients={}, user=uid).save()

    raw_recipes = {}
    raw_ids = []
    sp_recipes = []
    for recipe in slist.recipes:
        if recipe.startswith("sp_"):
            sp_recipes.append(recipe[3:])
            raw_ids.append(recipe)
        else:
            rr = Post.objects(id=recipe).get()
            raw_recipe = json.loads(rr.to_json())
            raw_recipe['id'] = raw_recipe["_id"]["$oid"]
            del raw_recipe['_id']
            raw_recipes[recipe] = raw_recipe
            raw_ids.append(recipe)

    if len(sp_recipes):
        rcode, all_recipes = make_spoonacular_api_call(f"recipes/informationBulk", "get",
                                                       params={"ids": ",".join(sp_recipes)})
        if rcode != 429:
            for recipe in all_recipes:
                raw_recipes[f"sp_{recipe['id']}"] = spoonacular_api_to_internal(recipe)
        else:
            to_return = [recipe for recipe in raw_ids if not recipe.startswith('sp_')]

    shopping_list = json.loads(slist.to_json())
    shopping_list['raw_recipes'] = raw_recipes
    shopping_list['recipes'] = raw_ids

    return {"code": 200, "data": shopping_list, "msg": None}, 200


@users.patch("/<uid>/shopping-list")
@authed
def patch_shopping_list(user, uid):
    rjson = request.get_json()
    slist = ShoppingList.objects.get(user=user.id)

    updated = slist.update(**rjson)
    slist.reload()

    return {"code": 200, "data": json.loads(slist.to_json()), "msg": None}, 200

@users.post("/<uid>/meal-plan")
@authed
def post_meal_plan(user, uid):
    rjson = request.get_json()

    new_list = MealPlan(**rjson, user=uid).save()

    return {"code": 200, "data": json.loads(new_list.to_json()), "msg": None}, 200


@users.get("/<uid>/meal-plan")
@authed
def get_meal_plan(user, uid):
    try:
        slist = MealPlan.objects.get(user=user.id)
    except DoesNotExist:
        slist = MealPlan(plan={}, user=uid).save()

    return {"code": 200, "data": json.loads(slist.to_json()), "msg": None}, 200


@users.patch("/<uid>/meal-plan")
@authed
def patch_meal_plan(user, uid):
    rjson = request.get_json()
    slist = MealPlan.objects.get(user=user.id)

    updated = slist.update(**rjson)
    slist.reload()

    return {"code": 200, "data": json.loads(slist.to_json()), "msg": None}, 200

@users.route("/testing", methods=["GET", "POST"])
def upload_file():
    if request.method == "POST":
        if request.form.get("id"):
            uid = int(request.form.get("id"))

            user = User_model.get_or_none(User_model.id == uid)
            user.username = request.form.get("username")
            user.bio = request.form.get("bio")
            user.name = request.form.get("name")
            user.email = request.form.get("email")
            user.favorite_posts = request.form.get("favorite_posts")
            user.following = request.form.get("following")
            user.followers = request.form.get("followers")
            user.save()
            return redirect(request.url)

        User_model.create(label=request.form.get("label"), emoji=request.form.get("emoji"), color=request.form.get("color"))
        return redirect(request.url)

    user_id = request.args.get('id', None)
    user_to_modify = {}
    if user_id:
        user_to_modify = User_model.get_or_none(User_model.id == user_id)
        if user_to_modify:
            user_to_modify = user_to_modify.to_dict()
        else:
            user_to_modify = {}
    return f"""
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>User Testing</title>
        </head>
        <body>
          <h1>Create/Modify User</h1>
          <form method=post enctype=multipart/form-data>
            ID <input type=text name=id value="{user_to_modify.get('id', '')}" readonly><br>
            Username <input type=text name=username value="{user_to_modify.get('username', "")}"><br>
            Bio <input type=text name=bio value="{user_to_modify.get('bio', "")}"><br>
            Name <input type=text name=name value="{user_to_modify.get('name', "")}"><br>
            Created At <input type=text name=created_at value="{user_to_modify.get('created_at', "")}"><br>
            Email <input type=text name=email value="{user_to_modify.get('email', "")}"><br>
            Account Flags <input type=text name=account_flags value="{user_to_modify.get('account_flags', "")}" readonly><br>
            Favorite Posts <input type=text name=favorite_posts value="{user_to_modify.get('favorite_posts', "")}"><br>
            Avatar <input type=text name=avatar value="{user_to_modify.get('avatar', "")}"><br>
            Following <input type=text name=following value="{user_to_modify.get('following', "")}"><br>
            Followers <input type=text name=followers value="{user_to_modify.get('followers', "")}"><br>
            Preferences <input type=text name=preferences value="{user_to_modify.get('preferences', "")}" readonly><br>
            <input type=submit value=Create/Edit>
          </form>
        </body>
        </html>
        """
