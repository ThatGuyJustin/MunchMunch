from peewee import AutoField, TextField
from playhouse.postgres_ext import ArrayField

from db import PostgresBase


@PostgresBase.register
class Tags(PostgresBase):
    class Meta:
        table_name = 'post_tags'

    id = AutoField()
    label = TextField(unique=True)
    emoji = TextField(unique=False, null=True)
    color = TextField(unique=False, null=True)
    constraints = ArrayField(TextField, default=[])

    def to_dict(self):
        return {
            'id': self.id,
            'label': self.label,
            'emoji': self.emoji,
            'color': self.color,
            'constraints': self.constraints
        }
