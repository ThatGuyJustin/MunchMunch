import mimetypes

from flask import Blueprint, send_file

from models.user import Users
from util.files import get_object

media = Blueprint('media', __name__)


@media.get("/avatars/<uid>/<media_hash>")
def get_avatar(uid, media_hash):
    user = Users.get_or_none(id=uid)
    if not user:
        return "User Not Found.", 404

    pfp_hash = user.avatar or "default.png"
    path = "default" if not user.avatar else uid

    picture = get_object(f"avatars/{path}", pfp_hash)

    if not picture:
        return "Picture Not Found.", 404

    return send_file(picture, as_attachment=False, mimetype=mimetypes.guess_type(pfp_hash)[0], download_name=pfp_hash)


@media.get("/posts/<pid>/<image>/<hash>")
def get_posts_media(pid, image, media_hash):
    # Get post

    # Get image type

    # Find image hash

    # Return Image
    pass
