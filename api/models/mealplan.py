from mongoengine import Document, IntField, ListField, StringField, DictField


class MealPlan(Document):
    user = IntField(primary_key=True)
    plan = DictField()