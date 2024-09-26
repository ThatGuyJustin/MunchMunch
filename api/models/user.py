import re

from peewee import AutoField, TextField
from playhouse.postgres_ext import ArrayField
from werkzeug.security import check_password_hash

from db import PostgresBase

email_regex = re.compile(r"(^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$)")


@PostgresBase.register
class Users(PostgresBase):
    class Meta:
        table_name = 'users'

    id = AutoField()
    username = TextField(unique=True)
    email = TextField(unique=True)
    account_flags = ArrayField(TextField, default=[])
    liked_posts = ArrayField(TextField, default=[])
    password = TextField()

    def to_dict(self):
        base = {"id": self.id, "username": self.username, "email": self.email}
        if self.account_flags:
            base["account_flags"] = self.account_flags
        if self.liked_posts:
            base["liked_posts"] = self.liked_posts
        return base

    @classmethod
    def login(cls, login, password):
        email = None
        username = None
        if re.match(email_regex, login):
            email = login
        else:
            username = login

        if email:
            user = cls.get_or_none(email=email)
        else:
            user = cls.get_or_none(username=username)

        if not user or not check_password_hash(user.password, password):
            return

        return user
