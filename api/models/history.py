import datetime

from bson import ObjectId
from mongoengine import Document, IntField, DictField, ObjectIdField, StringField, DateTimeField


class HistoryTypes:
    VIEW_RECIPE = 'VIEW'
    COOKED_RECIPE = 'COOKED'


class History(Document):
    id = ObjectIdField(primary_key=True, default=ObjectId)
    user = IntField(index=True)
    recipe = StringField()
    type = StringField()
    timestamp = DateTimeField(default=datetime.datetime.now(datetime.UTC))
