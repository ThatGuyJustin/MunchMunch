import datetime
from dataclasses import dataclass

from peewee import AutoField, IntegerField, TextField
from playhouse.postgres_ext import ArrayField, BinaryJSONField

from db import PostgresBase


class TicketStatus(object):
    PENDING = "pending"
    ASSIGNED = "assigned"
    RESOLVED = "resolved"


@dataclass
class TicketMessage:
    user: int
    message: str = ""
    timestamp: int = datetime.datetime.now().timestamp()
    admin_message: bool = False


@PostgresBase.register
class Ticket(PostgresBase):
    class Meta:
        table_name = 'admin_tickets'

    id = AutoField()
    user = IntegerField(null=False)
    assigned_to = IntegerField(null=True)
    subject = TextField(unique=False, null=False)
    status = TextField(unique=False, null=False, default="pending")
    messages = BinaryJSONField(default={'messages': []})

    def to_dict(self):
        return {
            'id': self.id,
            'user': self.user,
            'assigned_to': self.assigned_to,
            'subject': self.subject,
            'status': self.status,
            'messages': self.messages['messages']
        }
