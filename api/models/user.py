from peewee import AutoField, TextField
from playhouse.postgres_ext import ArrayField

from db import PostgresBase


@PostgresBase.register
class Users(PostgresBase):
    class Meta:
        table_name = 'users'

    id = AutoField()
    username = TextField(unique=True)
    email = TextField(unique=True)
    account_flags = ArrayField(TextField, default=[])
    liked_posts = ArrayField(TextField, default=[])
    # password = TextField()

    def to_dict(self):
        base = {"id": self.id, "username": self.username, "email": self.email}
        if self.account_flags:
            base["account_flags"] = self.account_flags
        if self.liked_posts:
            base["liked_posts"] = self.liked_posts
        return base