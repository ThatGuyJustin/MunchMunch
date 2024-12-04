from mongoengine import Document, IntField, ListField, DictField, StringField


# TODO: Shopping List Models
class ShoppingList(Document):
    user = IntField(primary_key=True)
    recipes = ListField(StringField(), default=[])
    ingredients = DictField(default={})
