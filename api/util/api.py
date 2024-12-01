import os

import requests

SPOONACULAR_BASE_API = "https://spoonacular-recipe-food-nutrition-v1.p.rapidapi.com/"
SPOONACULAR_API_KEY = os.getenv("SPOONACULAR_API_KEY") or None

BASE_HEADS = {
    "x-rapidapi-host": "spoonacular-recipe-food-nutrition-v1.p.rapidapi.com",
    "x-rapidapi-key": SPOONACULAR_API_KEY,
}


def make_spoonacular_api_call(path: str, method: str, params: dict = None, data: dict = None):
    request_method = getattr(requests, method)

    response = request_method(
        SPOONACULAR_BASE_API + path,
        headers=BASE_HEADS,
        params=params,
        json=data
    )

    return response.status_code, response.json() if len(response.content) != 0 else None


def spoonacular_api_to_internal(spoonacular_recipe):
    spoonacular_user = {
        "username": "spoonacular",
        "name": "Spoonacular",
        "id": 0,
        "account_flags": ["INTERNAL"],
    }

    base_recipe = {
        "id": f"sp_{spoonacular_recipe["id"]}",
        "user": spoonacular_user,
        "title": spoonacular_recipe["title"],
        "description": spoonacular_recipe["summary"],
        "time_to_cook": spoonacular_recipe["readyInMinutes"],
        "time_to_prepare": 0,
        "skill_level": 0,
        "reviews": [],
        "media": {
            "main": [
                f"https://img.spoonacular.com/recipes/{spoonacular_recipe['id']}-636x393.jpg"
            ]
        },
        "tags": spoonacular_recipe["diets"] + spoonacular_recipe["dishTypes"],
    }

    ingredients = {}
    steps = []

    for ingredient in spoonacular_recipe.get("extendedIngredients", []):
        ingredients[ingredient["name"]] = f"{ingredient["measures"]["us"]["amount"]} {ingredient["measures"]["us"].get("unitLong", "")}"

    base_recipe["ingredients"] = ingredients
    for super_step in spoonacular_recipe.get("analyzedInstructions", []):
        for actual_steps in super_step["steps"]:
            steps.append(actual_steps["step"])
    base_recipe["steps"] = steps

    return base_recipe
