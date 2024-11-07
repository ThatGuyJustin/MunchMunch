from mongoengine import Document, IntField, ObjectIdField, ListField, DictField


# TODO: Shopping List Models
class ShoppingList(Document):
    user = IntField(primary_key=True)
    recipes = ListField(ObjectIdField(), default=[])
    ingredients = DictField(default={})
