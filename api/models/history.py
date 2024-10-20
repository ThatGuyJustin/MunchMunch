import datetime

from mongoengine import Document, IntField, DictField, ObjectIdField, StringField, DateTimeField


class HistoryTypes:
    VIEW_RECIPE = 'VIEW'
    COOKED_RECIPE = 'COOKED'


class History(Document):
    user = IntField(primary_key=True)
    recipe = ObjectIdField()
    type = StringField()
    timestamp = DateTimeField(default=datetime.datetime.now(datetime.UTC))
