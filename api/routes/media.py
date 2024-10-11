import hashlib
import mimetypes
import os

from flask import Blueprint, send_file, request
from werkzeug.utils import secure_filename

from models.user import Users
from util.auth import authed
from util.files import get_object, allowed_file, upload_object

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
            filename = secure_filename(file.filename)
            new_filename = hashlib.md5(file.read()).hexdigest()
            file.seek(0)
            ext = secure_filename(file.filename).split(".")[-1]
            new_filename = new_filename + "." + ext
            size = os.fstat(file.fileno()).st_size
            upload_object(new_filename, file, size, f"avatars/{user.id}")

            user.avatar = new_filename
            user.save()

            return new_filename, 200


@media.get("/posts/<pid>/<image>/<hash>")
def get_posts_media(pid, image, media_hash):
    # Get post

    # Get image type

    # Find image hash

    # Return Image
    pass
