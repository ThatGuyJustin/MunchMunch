import datetime
from dataclasses import dataclass

from bson import ObjectId
from mongoengine import Document, ObjectIdField, StringField, DictField, IntField, ListField, DateTimeField


class Post(Document):
    id = ObjectIdField(primary_key=True, default=ObjectId)
    created_at = DateTimeField(default=datetime.datetime.now(datetime.UTC))
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
    media = DictField(default={'main': [], 'step': []})


@dataclass
class Review:
    user: int
    rating: int = 0
    comment: str = ""
