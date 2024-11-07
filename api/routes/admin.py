from dataclasses import asdict

from flask import Blueprint, request

from models.ticket import Ticket, TicketMessage
from util.auth import authed, can_do_admin_requests

admin = Blueprint('admin', __name__)


@admin.post("/requests")
@authed
def make_request(user):
    rjson = request.json
    if not rjson:
        return {'code': 406, "data": {}, "msg": "Missing Data"}, 406

    if "subject" not in rjson:
        return {'code': 406, "data": {}, "msg": "Missing Subject"}, 406

    if "message" not in rjson:
        return {'code': 406, "data": {}, "msg": "Missing Message"}, 406

    initial_message = TicketMessage(user=user.id, message=rjson["message"], admin_message=('ADMIN' in user.account_flags))

    ticket = Ticket.create(user=user.id, subject=rjson["subject"], messages={'messages': [asdict(initial_message)]})

    return {'code': 200, "data": ticket.to_dict(), "msg": f"Ticket {ticket.id} Created"}, 200


@admin.get("/requests")
@authed
def get_pending_requests(user):
    if not can_do_admin_requests(user):
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    return {'code': 501, "data": {}, "msg": "NYI"}, 501


@admin.get("/requests/<request_id>")
@authed
def get_request(user, request_id):
    req = Ticket.get_or_none(id=request_id)
    if not req:
        return {'code': 404, "data": {}, "msg": "Request not found."}, 404

    if req.user != user.id and not can_do_admin_requests(user):
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    return {'code': 200, "data": req.to_dict(), "msg": f"Ticket {req.id}"}, 200


@admin.post("/requests/<request_id>/messages")
@authed
def add_message(user, request_id):
    req = Ticket.get_or_none(id=request_id)
    if not req:
        return {'code': 404, "data": {}, "msg": "Request not found."}, 404

    if req.user != user.id and not can_do_admin_requests(user):
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    rjson = request.json
    if not rjson:
        return {'code': 406, "data": {}, "msg": "Missing Data"}, 406

    msg = TicketMessage(user=user.id, message=rjson["message"], admin_message=('ADMIN' in user.account_flags))

    req.messages['messages'].append(asdict(msg))
    req.save()

    return {'code': 200, "data": req.to_dict(), "msg": f"Message Added To Ticket {req.id}"}, 200


@admin.patch("/requests/<request_id>")
@authed
def update_request(user, request_id):

    VALID_UPDATES = ["status", "assigned_to"]

    req = Ticket.get_or_none(id=request_id)
    if not req:
        return {'code': 404, "data": {}, "msg": "Request not found."}, 404

    if req.user != user.id and not can_do_admin_requests(user):
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    rjson = request.json
    if not rjson:
        return {'code': 406, "data": {}, "msg": "Missing Data"}, 406

    if ("status" in rjson and not can_do_admin_requests(user)) and "status" != "resolved":
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    if not can_do_admin_requests(user) and "assigned_to" in rjson:
        return {'code': 401, "data": {}, "msg": "Not Authorized."}, 401

    if "assigned_to" in rjson:
        req.assigned_to = rjson["assigned_to"]
        req.save()
        return {'code': 200, "data": {}, "msg": f"Ticket {req.id} Updated"}, 200

    if "status" in rjson:
        req.status = rjson["status"]
        req.save()
        return {'code': 200, "data": {}, "msg": f"Ticket {req.id} Updated"}, 200

    return {'code': 400, "data": {}, "msg": "Ticket Not Updated."}, 400
