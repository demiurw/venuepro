<template>
  <Card class="border-border/60">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
            <Users class="h-5 w-5 text-primary" />
          </div>
          <div>
            <CardTitle class="text-base">{{ title }}</CardTitle>
            <p class="text-sm text-muted-foreground">{{ description }}</p>
          </div>
        </div>
        <StatusBadge :status="status" />
      </div>
    </CardHeader>

    <CardContent class="pt-0">
      <!-- Status Information -->
      <div class="space-y-3 mb-4">
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Account Status:</span>
          <span class="font-medium">{{ statusLabel }}</span>
        </div>
        
        <div v-if="lastActive" class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Last Active:</span>
          <span class="font-medium">{{ formatLastActive(lastActive) }}</span>
        </div>

        <div v-if="email" class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Email:</span>
          <span class="font-medium truncate max-w-[200px]">{{ email }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2">
        <Button
          v-if="canActivate"
          @click="$emit('activate')"
          size="sm"
          variant="outline"
          class="flex-1 h-9 border-green-200 text-green-700 hover:bg-green-50"
        >
          <UserCheck class="h-4 w-4 mr-2" />
          Activate
        </Button>

        <Button
          v-if="canDeactivate"
          @click="$emit('deactivate')"
          size="sm"
          variant="outline"
          class="flex-1 h-9 border-yellow-200 text-yellow-700 hover:bg-yellow-50"
        >
          <UserX class="h-4 w-4 mr-2" />
          Deactivate
        </Button>

        <Button
          v-if="canResendVerification"
          @click="$emit('resend-verification')"
          size="sm"
          variant="outline"
          class="flex-1 h-9 border-blue-200 text-blue-700 hover:bg-blue-50"
        >
          <RefreshCw class="h-4 w-4 mr-2" />
          Resend Email
        </Button>

        <Button
          v-if="showEditButton"
          @click="$emit('edit')"
          size="sm"
          variant="outline"
          class="h-9 px-3"
        >
          <Edit class="h-4 w-4" />
        </Button>
      </div>

      <!-- Warning Messages -->
      <div v-if="warningMessage" class="mt-3">
        <Alert variant="warning" class="border-warning/30">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription class="text-sm">
            {{ warningMessage }}
          </AlertDescription>
        </Alert>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import StatusBadge from '@/components/StatusBadge.vue'
import {
  Users,
  UserCheck,
  UserX,
  RefreshCw,
  Edit,
  AlertTriangle
} from 'lucide-vue-next'

export interface UserStatusCardProps {
  title: string
  description?: string
  status: 'active' | 'inactive' | 'pending'
  email?: string
  lastActive?: string
  showEditButton?: boolean
}

const props = withDefaults(defineProps<UserStatusCardProps>(), {
  showEditButton: true
})

const emit = defineEmits<{
  activate: []
  deactivate: []
  'resend-verification': []
  edit: []
}>()

const statusConfig = {
  active: 'Active',
  inactive: 'Inactive',
  pending: 'Pending Verification'
}

const statusLabel = computed(() => statusConfig[props.status])

const canActivate = computed(() => props.status === 'inactive')
const canDeactivate = computed(() => props.status === 'active')
const canResendVerification = computed(() => props.status === 'pending')

const warningMessage = computed(() => {
  switch (props.status) {
    case 'inactive':
      return 'This user cannot access the system while their account is deactivated.'
    case 'pending':
      return 'This user must verify their email address before they can access the system.'
    default:
      return null
  }
})

const formatLastActive = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMs = now.getTime() - date.getTime()
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))

  if (diffInDays === 0) {
    return 'Today'
  } else if (diffInDays === 1) {
    return 'Yesterday'
  } else if (diffInDays < 7) {
    return `${diffInDays} days ago`
  } else {
    return date.toLocaleDateString()
  }
}
</script>