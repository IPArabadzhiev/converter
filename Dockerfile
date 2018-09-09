FROM fauria/lamp

WORKDIR /var/www/html

RUN apt-get update -y && \
    apt-get install ffmpeg -y

COPY . .

