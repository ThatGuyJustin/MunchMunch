from flask import Blueprint, request, redirect

from models.tags import Tags
from util.auth import authed

tags = Blueprint('tags', __name__)


@tags.get("")
def get_tags():
    base_query = None
    if 'query' in request.args:
        base_query = Tags.select(Tags).where(Tags.label.contains(request.args.get('query')))
    else:
        base_query = Tags.select(Tags)

    query = list(base_query.order_by(Tags.label.asc()))

    return [q.to_dict() for q in query], 200


@tags.route("/testing", methods=["GET", "POST"])
def upload_file():
    if request.method == "POST":
        print(request.form.to_dict().items())
        if request.form.get("id"):
            tid = int(request.form.get("id"))

            tag = Tags.get_or_none(Tags.id == tid)
            tag.label = request.form.get("label")
            tag.emoji = request.form.get("emoji")
            tag.color = request.form.get("color")
            tag.save()
            return redirect(request.url)

        Tags.create(label=request.form.get("label"), emoji=request.form.get("emoji"), color=request.form.get("color"))
        return redirect(request.url)

    tag_id = request.args.get('id', None)
    tag_to_modify = {}
    if tag_id:
        tag_to_modify = Tags.get_or_none(Tags.id == tag_id)
        if tag_to_modify:
            tag_to_modify = tag_to_modify.to_dict()
        else:
            tag_to_modify = {}
    return f"""
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Tag Testing</title>
        </head>
        <body>
          <h1>Create Tag</h1>
          <form method=post enctype=multipart/form-data>
            ID <input type=text name=id value="{tag_to_modify.get('id', '')}" readonly><br>
            Label <input type=text name=label value="{tag_to_modify.get('label', "")}"><br>
            Emoji <input type=text name=emoji value="{tag_to_modify.get('emoji', "")}"><br>
            Color <input type=text name=color value="{tag_to_modify.get('color', "")}"><br>
            <input type=submit value=Create/Edit>
          </form>
        </body>
        </html>
        """