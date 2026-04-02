FROM node:24-alpine

WORKDIR /usr/src/app

RUN corepack enable

COPY package.json yarn.lock ./

RUN yarn install --immutable

COPY . .

EXPOSE 5173

CMD ["yarn", "dev", "--host", "0.0.0.0", "--port", "5173"]
