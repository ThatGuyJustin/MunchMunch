import hashlib
import mimetypes
import os

from flask import Blueprint, send_file, request
from mongoengine import DoesNotExist

from models.post import Post
from models.user import Users
from util.auth import authed
from util.files import get_object, allowed_file, upload_object, generate_filename

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


@media.post("/avatars")
@authed
def update_avatar(user):
    if request.method == "POST":
        # check if the post request has the file part
        if "file" not in request.files:
            return "Missing File.", 400
        file = request.files["file"]
        # If the user does not select a file, the browser submits an
        # empty file without a filename.
        if file.filename == "":
            return "Missing File.", 400
        if file and allowed_file(file.filename):
            filename = generate_filename(file)
            size = os.fstat(file.fileno()).st_size
            upload_object(filename, file, size, f"avatars/{user.id}")

            user.avatar = filename
            user.save()

            return filename, 200


@media.get("/recipe/<rid>/<image>/<hash>")
def get_posts_media(rid, image, media_hash):
    # Get post

    # Get image type

    # Find image hash

    # Return Image
    pass


@media.post("/recipe/<rid>/<media>")
def upload_recipe_media(rid, media_type):

    VALID_TYPES = ["main", "step"]

    try:
        recipe = Post.objects.get(id=rid)
    except DoesNotExist:
        return {
            'code': 404,
            'msg': "Recipe not found",
            'data': {}
        }, 404

    real_media_type = media_type

    if "file" not in request.files:
        return "Missing File.", 400

    file = request.files["file"]
    # If the user does not select a file, the browser submits an
    # empty file without a filename.
    if file.filename == "":
        return "Missing File.", 400
    if file and allowed_file(file.filename):
        filename = generate_filename(file)
        size = os.fstat(file.fileno()).st_size
        upload_object(filename, file, size, f"recipes/{rid}/{real_media_type}")

