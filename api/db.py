import os
from peewee import Model, OP
from playhouse.postgres_ext import PostgresqlExtDatabase

postgres_db = PostgresqlExtDatabase(
    os.getenv("DATABASE_NAME", "MunchMunch"),
    host=os.getenv("DATABASE_HOST", "192.168.1.11"),
    user=os.getenv("DATABASE_USER", "postgres"),
    password=os.getenv("DATABASE_PASSWORD", "BigTitties"),
)

REGISTERED_MODELS = []


class PostgresBase(Model):
    class Meta:
        database = postgres_db

    @staticmethod
    def register(cls):
        cls.create_table(True)
        if hasattr(cls, 'SQL'):
            postgres_db.execute_sql(cls.SQL)

        REGISTERED_MODELS.append(cls)
        return cls


def init_db():
    # TODO: Setup later!
    return postgres_db
