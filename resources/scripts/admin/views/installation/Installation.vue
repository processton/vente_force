<template>
  <div class="flex flex-col items-center justify-between w-full pt-10">
    <img
      id="logo-crater"
      src="/img/crater-logo.png"
      alt="Crater Logo"
      class="h-12 mb-5 md:mb-10"
    />

    <BaseWizard
      :steps="7"
      :current-step="currentStepNumber"
      @click="onNavClick"
    >
      <component :is="stepComponent" @next="onStepChange" />
    </BaseWizard>
  </div>
</template>

<script>
import { ref } from 'vue'
import Step1RequirementsCheck from './Step1RequirementsCheck.vue'
import Step2PermissionCheck from './Step2PermissionCheck.vue'
import Step3DatabaseConfig from './Step3DatabaseConfig.vue'
import Step4VerifyDomain from './Step4VerifyDomain.vue'
import Step5EmailConfig from './Step5EmailConfig.vue'
import Step6AccountSettings from './Step6AccountSettings.vue'
import Step7CompanyInfo from './Step7CompanyInfo.vue'
import Step8CompanyPreferences from './Step8CompanyPreferences.vue'
import { useInstallationStore } from '@/scripts/admin/stores/installation'
import { useRouter } from 'vue-router'

export default {


  setup() {


    const router = useRouter()
    const installationStore = useInstallationStore()
    checkCurrentProgress()
    async function checkCurrentProgress() {
      try {
        await installationStore.addInstallationFinish()
        // await installationStore.installationLogin()
        let driverRes = await installationStore.checkAutheticated()

        if (driverRes.data) {
          emit('next', 4)
        }

        isSaving.value = false
      } catch (e) {
        notificationStore.showNotification({
          type: 'error',
          message: t('wizard.verify_domain.failed'),
        })

        isSaving.value = false
      }
    }


    function onNavClick(e) {}

    return {
      stepComponent,
      currentStepNumber,
      onStepChange,
      saveStepProgress,
      onNavClick,
    }
  },
}
</script>
