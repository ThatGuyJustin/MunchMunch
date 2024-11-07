import datetime
import re

from peewee import AutoField, TextField, IntegerField, DateTimeField
from playhouse.postgres_ext import ArrayField, JSONField
from werkzeug.security import check_password_hash

from db import PostgresBase

email_regex = re.compile(r"(^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$)")


@PostgresBase.register
class Users(PostgresBase):
    class Meta:
        table_name = 'users'

    id = AutoField()
    username = TextField(unique=True)
    bio = TextField(null=True)
    name = TextField(null=True)
    created_at = DateTimeField(default=datetime.datetime.now(datetime.UTC))
    email = TextField(unique=True)
    account_flags = ArrayField(TextField, default=[])
    favorite_posts = ArrayField(TextField, default=[])
    password = TextField()
    avatar = TextField(null=True)
    following = ArrayField(IntegerField, default=[])
    followers = ArrayField(IntegerField, default=[])
    preferences = JSONField(default={"dietary": []})

    def to_dict(self):
        base = {"id": self.id, "username": self.username, "bio": self.bio, "name": self.name or self.username, "email": self.email, "avatar": self.avatar or "default.png",
                'following': self.following, 'followers': self.followers, 'preferences': self.preferences, 'created_at': self.created_at, 'account_flags': self.account_flags, 'favorite_posts': self.favorite_posts}
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
