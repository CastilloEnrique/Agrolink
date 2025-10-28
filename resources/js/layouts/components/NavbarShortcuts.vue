<script setup>
// 💡 Lista de atajos actualizada para Agrolink
const shortcuts = [
  {
    icon: 'tabler-store',
    title: 'Catálogo',
    subtitle: 'Ver productos',
    to: { name: 'apps-consumidor-catalogo' },
  },
  {
    icon: 'tabler-calendar',
    title: 'Calendario', // 👈 Dejamos este, como pediste
    subtitle: 'Ver citas (Demo)',
    to: { name: 'apps-calendar' },
  },
  {
    icon: 'tabler-upload',
    title: 'Publicar',
    subtitle: 'Nuevo producto',
    to: { name: 'apps-productor-publicar' },
  },
  {
    icon: 'tabler-packages',
    title: 'Mis Productos',
    subtitle: 'Gestionar stock',
    to: { name: 'apps-productor-mis-productos' },
  },
  {
    icon: 'tabler-user-circle',
    title: 'Mi Perfil',
    subtitle: 'Ver mi perfil',
    to: { name: 'apps-productor-perfil' },
  },
  {
    icon: 'tabler-settings',
    title: 'Configuración',
    subtitle: 'Ajustes de cuenta',
    to: {
      name: 'pages-account-settings-tab',
      params: { tab: 'account' },
    },
  },
]
</script>

<template>
  <Shortcuts :shortcuts="shortcuts" />
</template>














<!--<script setup>-->
<!--const shortcuts = [-->
<!--  {-->
<!--    icon: 'tabler-calendar',-->
<!--    title: 'Calendar',-->
<!--    subtitle: 'Appointments',-->
<!--    to: { name: 'apps-calendar' },-->
<!--  },-->
<!--  {-->
<!--    icon: 'tabler-file-dollar',-->
<!--    title: 'Invoice App',-->
<!--    subtitle: 'Manage Accounts',-->
<!--    to: { name: 'apps-invoice-list' },-->
<!--  },-->
<!--  {-->
<!--    icon: 'tabler-user',-->
<!--    title: 'Users',-->
<!--    subtitle: 'Manage Users',-->
<!--    to: { name: 'apps-user-list' },-->
<!--  },-->
<!--  {-->
<!--    icon: 'tabler-users',-->
<!--    title: 'Role Management',-->
<!--    subtitle: 'Permission',-->
<!--    to: { name: 'apps-roles' },-->
<!--  },-->
<!--  {-->
<!--    icon: 'tabler-device-desktop-analytics',-->
<!--    title: 'Dashboard',-->
<!--    subtitle: 'Dashboard Analytics',-->
<!--    to: { name: 'dashboards-analytics' },-->
<!--  },-->
<!--  {-->
<!--    icon: 'tabler-settings',-->
<!--    title: 'Settings',-->
<!--    subtitle: 'Account Settings',-->
<!--    to: {-->
<!--      name: 'pages-account-settings-tab',-->
<!--      params: { tab: 'account' },-->
<!--    },-->
<!--  },-->
<!--]-->
<!--</script>-->

<!--<template>-->
<!--  <Shortcuts :shortcuts="shortcuts" />-->
<!--</template>-->
