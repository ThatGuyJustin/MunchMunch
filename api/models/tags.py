from peewee import AutoField, TextField
from playhouse.postgres_ext import ArrayField

from db import PostgresBase


@PostgresBase.register
class Tags(PostgresBase):
    class Meta:
        table_name = 'post_tags'

    id = AutoField()
    label = TextField(unique=True)
    emoji = TextField(unique=False)
    color = TextField(unique=False)
    constraints = ArrayField(TextField())
