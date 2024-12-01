import json

from models.post import Post
from util.api import make_spoonacular_api_call, spoonacular_api_to_internal


def search_recipes(request_args):

    to_return = []

    general_name_query: str = request_args.get("query", None)
    potential_ingredients_query: list = request_args.get("ingredients", "").split(",")

    # First get the system recipes, and grab more if we have an insignificant amount of results.
    query = {}
    if len(potential_ingredients_query) > 0:
        query['ingredients__in'] = potential_ingredients_query
    if general_name_query:
        query['title__icontains'] = general_name_query
    system_recipes = Post.objects.filter(**query)

    for srecipe in system_recipes:
        base_json = json.loads(srecipe.to_json())
        base_json['id'] = base_json["_id"]["$oid"]
        del base_json['_id']
        to_return.append(base_json)

    if len(to_return) < 50:
        # YES, WE NEED MORE RECIPES! TO SPOONACULAR WE GO!
        params = {
            "addRecipeInformation": "true"
        }
        if len(potential_ingredients_query) > 0:
            params["includeIngredients"] = request_args.get("ingredients")
        if general_name_query:
            params['query'] = general_name_query
        params['number'] = 50 - len(to_return)
        rcode, data = make_spoonacular_api_call("recipes/complexSearch", "get", params=params)
        print(rcode)
        print(data)
        for recipe in data['results']:
            to_return.append(spoonacular_api_to_internal(recipe))

    return to_return