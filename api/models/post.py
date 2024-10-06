from bson import ObjectId
from mongoengine import Document, ObjectIdField, StringField, DictField, IntField, ListField


class Post(Document):
    id = ObjectIdField(primary_key=True, default=ObjectId)
    user = IntField()
    title = StringField()
    description = StringField()
    tags = ListField(IntField())
    steps = ListField(StringField())
    ingredients = DictField()
    time_to_cook = IntField()
    time_to_prepare = IntField()
    skill_level = IntField()
    reviews = ListField(DictField())
