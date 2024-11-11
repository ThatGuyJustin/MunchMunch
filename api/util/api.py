import os

SPOONACULAR_BASE_API = "https://spoonacular-recipe-food-nutrition-v1.p.rapidapi.com"
SPOONACULAR_API_KEY = os.getenv("SPOONACULAR_API_KEY") or None

BASE_HEADS = {
    ""
}

def