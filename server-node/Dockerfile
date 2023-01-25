FROM node:18-slim

COPY package.json /app/package.json
COPY main.js /app/main.js

WORKDIR /app
RUN yarn

ENTRYPOINT yarn start
