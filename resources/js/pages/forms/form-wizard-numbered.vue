<script setup>
// eslint-disable-next-line import/no-unresolved
import DemoFormWizardNumberedBasic from '@/views/demos/forms/form-wizard/form-wizard-numbered/DemoFormWizardNumberedBasic.vue'
// eslint-disable-next-line import/no-unresolved
import DemoFormWizardNumberedModernBasic from '@/views/demos/forms/form-wizard/form-wizard-numbered/DemoFormWizardNumberedModernBasic.vue'
// eslint-disable-next-line import/no-unresolved
//import DemoFormWizardNumberedModernVertical from '@/views/demos/forms/form-wizard/form-wizard-numbered/DemoFormWizardNumberedModernVertical.vue'
// eslint-disable-next-line import/no-unresolved
import DemoFormWizardNumberedVertical from '@/views/demos/forms/form-wizard/form-wizard-numbered/DemoFormWizardNumberedVertical.vue'
</script>

<template>
  <VRow>
    <!-- 👉 Basic -->
    <VCol cols="12">
      <AppCardCode
        variant="outlined"
        title="Basic"
      >
        <DemoFormWizardNumberedBasic />
      </AppCardCode>
    </VCol>

    <!-- 👉 Vertical -->
    <VCol cols="12">
      <AppCardCode
        variant="outlined"
        title="Vertical"
      >
        <DemoFormWizardNumberedVertical />
      </AppCardCode>
    </VCol>
  </VRow>

  <VDivider class="my-10 mx-n6" />

  <h3 class="text-h3 my-4">
    Modern
  </h3>

  <VRow>
    <!-- 👉 Modern Vertical -->
    <VCol cols="12">
      <AppCardCode
        variant="outlined"
        title="Modern Vertical"
      >
        <!--        <DemoFormWizardNumberedModernVertical /> -->
      </AppCardCode>
    </VCol>

    <!-- 👉 Modern Basic -->
    <VCol cols="12">
      <AppCardCode
        variant="outlined"
        title="Modern Basic"
      >
        <DemoFormWizardNumberedModernBasic />
      </AppCardCode>
    </VCol>
  </VRow>
</template>
