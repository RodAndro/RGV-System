FROM node:24-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --omit=dev

COPY src ./src

ARG APP_VERSION=dev
ENV APP_VERSION=${APP_VERSION}
ENV NODE_ENV=production
ENV PORT=3000

LABEL itsd85.lab=true

USER node

EXPOSE 3000

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
  CMD node -e "fetch('http://127.0.0.1:3000/health').then(async r => { const d = await r.json(); process.exit(r.ok && d.status === 'ok' && d.version === process.env.APP_VERSION ? 0 : 1); }).catch(() => process.exit(1))"

CMD ["node", "src/server.js"]
