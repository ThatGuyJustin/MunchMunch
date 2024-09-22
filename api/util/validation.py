import re

from models.user import Users

username_regex = re.compile(r"^[a-zA-Z0-9](_(?!(\.|_))|\.(?!(_|\.))|[a-zA-Z0-9]){4,18}[a-zA-Z0-9]$")


def validate_username(username):
    if not re.match(username_regex, username):
        return False

    if Users.get_or_none(username=username):
        return False

    if "admin" in username or "munchmunch" in username:
        return False

    return True


def validate_email(email):
    if Users.get_or_none(email=email):
        return False
    else:
        return True
