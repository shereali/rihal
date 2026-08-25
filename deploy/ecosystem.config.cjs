module.exports = {
  apps: [
    {
      name: 'rihal-nuxt',
      cwd: '/var/www/rihal/nuxt',
      script: 'node',
      args: '.output/server/index.mjs',
      instances: 'max',
      exec_mode: 'cluster',
      env: {
        NODE_ENV: 'production',
        NUXT_PUBLIC_API_BASE: 'https://yourdomain.com/api/v1',
      },
    },
    {
      name: 'rihal-queue',
      cwd: '/var/www/rihal/laravel',
      script: 'artisan',
      args: 'queue:work --queue=notifications,default --tries=3 --max-time=3600 --sleep=3',
      instances: 1,
      autorestart: true,
      env: {
        APP_ENV: 'production',
      },
    },
  ],
};
