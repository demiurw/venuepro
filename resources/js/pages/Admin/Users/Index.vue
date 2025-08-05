<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    title="User Management" 
                    description="Manage users, roles, and access permissions for your organization"
                />
                <Button 
                    @click="createUser"
                    class="flex items-center gap-2 h-10 px-4 rounded-xl font-medium transition-smooth"
                >
                    <UserPlus class="h-4 w-4" />
                    Add New User
                </Button>
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search users by name, email, or role..."
                        class="pl-10 h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                    />
                </div>
                <div class="flex gap-2">
                    <select 
                        v-model="statusFilter"
                        class="h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none min-w-[120px]"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                    <select 
                        v-model="roleFilter"
                        class="h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none min-w-[160px]"
                    >
                        <option value="">All Roles</option>
                        <option value="hod">Head of Department</option>
                        <option value="booking_agent">Booking Agent</option>
                        <option value="invitee">Invitee</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg font-semibold">Users ({{ filteredUsers.length }})</CardTitle>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Users class="h-4 w-4" />
                            {{ stats.total_users }} total users
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border/50">
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">User</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Email</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Role</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Status</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Last Active</th>
                                    <th class="text-right p-4 text-sm font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="user in paginatedUsers" 
                                    :key="user.id"
                                    class="border-b border-border/30 hover:bg-muted/30 transition-colors"
                                >
                                    <!-- User Info -->
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span class="text-sm font-medium text-primary">
                                                    {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground">{{ user.first_name }} {{ user.last_name }}</p>
                                                <p class="text-sm text-muted-foreground">ID: {{ user.id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Email -->
                                    <td class="p-4">
                                        <p class="text-sm text-foreground">{{ user.email }}</p>
                                    </td>
                                    
                                    <!-- Role -->
                                    <td class="p-4">
                                        <span 
                                            :class="getRoleBadgeClass(user.user_type)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ getUserTypeDisplayName(user.user_type) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="p-4">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger>
                                                    <StatusBadge :status="user.status" />
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>{{ getStatusDescription(user.status) }}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </td>
                                    
                                    <!-- Last Active -->
                                    <td class="p-4">
                                        <p class="text-sm text-muted-foreground">{{ formatDate(user.last_active) }}</p>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="p-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <!-- Status Action Button -->
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        <Button
                                                            @click="toggleUserStatus(user)"
                                                            variant="ghost"
                                                            size="sm"
                                                            :class="getStatusActionClass(user.status)"
                                                            class="h-8 w-8 p-0 rounded-lg"
                                                        >
                                                            <UserCheck v-if="user.status === 'inactive'" class="h-4 w-4" />
                                                            <UserX v-else-if="user.status === 'active'" class="h-4 w-4" />
                                                            <Clock v-else class="h-4 w-4" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>{{ getStatusActionTooltip(user.status) }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>

                                            <!-- Edit Button -->
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        <Button
                                                            @click="editUser(user.id)"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-8 w-8 p-0 rounded-lg hover:bg-primary/10"
                                                        >
                                                            <Edit class="h-4 w-4" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>Edit user</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>

                                            <!-- Delete Button -->
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        <Button
                                                            @click="deleteUser(user.id)"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-8 w-8 p-0 rounded-lg hover:bg-destructive/10 hover:text-destructive"
                                                        >
                                                            <Trash2 class="h-4 w-4" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>Delete user</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-16">
                        <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                            <Users class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-medium text-foreground mb-2">No users found</h3>
                        <p class="text-sm text-muted-foreground mb-6 max-w-sm text-center">
                            {{ searchQuery || statusFilter || roleFilter ? 'Try adjusting your search or filters' : 'Get started by adding your first user' }}
                        </p>
                        <Button @click="createUser" class="flex items-center gap-2">
                            <UserPlus class="h-4 w-4" />
                            Add New User
                        </Button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="filteredUsers.length > 0" class="flex items-center justify-between p-4 border-t border-border/50">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ startIndex + 1 }}-{{ Math.min(endIndex, filteredUsers.length) }} of {{ filteredUsers.length }} users
                        </p>
                        <div class="flex items-center gap-2">
                            <Button
                                @click="previousPage"
                                :disabled="currentPage === 1"
                                variant="outline"
                                size="sm"
                                class="h-8 px-3 rounded-lg"
                            >
                                <ChevronLeft class="h-4 w-4" />
                                Previous
                            </Button>
                            <div class="flex items-center gap-1">
                                <template v-for="page in visiblePages" :key="page">
                                    <Button
                                        v-if="page !== '...'"
                                        @click="goToPage(page as number)"
                                        :variant="currentPage === page ? 'default' : 'ghost'"
                                        size="sm"
                                        class="h-8 w-8 p-0 rounded-lg"
                                    >
                                        {{ page }}
                                    </Button>
                                    <span v-else class="px-2 text-muted-foreground">...</span>
                                </template>
                            </div>
                            <Button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                variant="outline"
                                size="sm"
                                class="h-8 px-3 rounded-lg"
                            >
                                Next
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Toast Notifications -->
        <Toast />
    </AppLayout>
</template>

<script setup lang="ts">
import { withDefaults, computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { toast } from '@/composables/useToast'
import {
    UserPlus,
    Search,
    Users,
    Edit,
    Trash2,
    ChevronLeft,
    ChevronRight,
    UserCheck,
    UserX,
    Clock
} from 'lucide-vue-next'

interface User {
    id: number
    first_name: string
    last_name: string
    email: string
    user_type: string
    status: 'active' | 'inactive' | 'pending'
    last_active: string
    created_at: string
}

interface Props {
    users?: User[]
    stats?: {
        total_users: number
        active_users: number
        pending_users: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    users: () => [],
    stats: () => ({
        total_users: 0,
        active_users: 0,
        pending_users: 0
    })
})

// Search and filter state
const searchQuery = ref('')
const statusFilter = ref('')
const roleFilter = ref('')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = 10

// Computed properties
const filteredUsers = computed(() => {
    let filtered = Array.isArray(props.users) ? props.users : []

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(user => 
            user.first_name.toLowerCase().includes(query) ||
            user.last_name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.user_type.toLowerCase().includes(query)
        )
    }

    // Status filter
    if (statusFilter.value) {
        filtered = filtered.filter(user => user.status === statusFilter.value)
    }

    // Role filter
    if (roleFilter.value) {
        filtered = filtered.filter(user => user.user_type === roleFilter.value)
    }

    return filtered
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage))

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => startIndex.value + itemsPerPage)

const paginatedUsers = computed(() => {
    const users = filteredUsers.value
    if (!Array.isArray(users)) return []
    return users.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
    const pages: (number | string)[] = []
    const total = totalPages.value
    const current = currentPage.value

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i)
        }
    } else {
        pages.push(1)
        
        if (current > 3) {
            pages.push('...')
        }
        
        const start = Math.max(2, current - 1)
        const end = Math.min(total - 1, current + 1)
        
        for (let i = start; i <= end; i++) {
            if (!pages.includes(i)) {
                pages.push(i)
            }
        }
        
        if (current < total - 2) {
            pages.push('...')
        }
        
        pages.push(total)
    }

    return pages
})

// Functions
const getUserTypeDisplayName = (userType: string) => {
    const names = {
        'hod': 'Head of Department',
        'booking_agent': 'Booking Agent',
        'invitee': 'Invitee'
    }
    return names[userType as keyof typeof names] || userType
}

const getRoleBadgeClass = (userType: string) => {
    const classes = {
        'hod': 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300',
        'booking_agent': 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
        'invitee': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
    }
    return classes[userType as keyof typeof classes] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
}

const getStatusDescription = (status: string) => {
    const descriptions = {
        'active': 'User can access the system normally',
        'inactive': 'User account is disabled',
        'pending': 'User needs to verify their account'
    }
    return descriptions[status as keyof typeof descriptions] || 'Unknown status'
}

const getStatusActionClass = (status: string) => {
    const classes = {
        'active': 'hover:bg-yellow-100 hover:text-yellow-600',
        'inactive': 'hover:bg-green-100 hover:text-green-600',
        'pending': 'hover:bg-blue-100 hover:text-blue-600'
    }
    return classes[status as keyof typeof classes] || 'hover:bg-gray-100'
}

const getStatusActionTooltip = (status: string) => {
    const tooltips = {
        'active': 'Deactivate user',
        'inactive': 'Activate user',
        'pending': 'Resend verification email'
    }
    return tooltips[status as keyof typeof tooltips] || 'Toggle status'
}

const formatDate = (dateString: string) => {
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

// Pagination functions
const goToPage = (page: number) => {
    currentPage.value = page
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

// Navigation functions
const createUser = () => {
    router.visit('/admin/users/create')
}

const editUser = (userId: number) => {
    router.visit(`/admin/users/${userId}/edit`)
}

const toggleUserStatus = (user: User) => {
    const actions = {
        'active': {
            action: 'deactivate',
            message: `Are you sure you want to deactivate ${user.first_name} ${user.last_name}? They will lose access to the system.`,
            endpoint: `/admin/users/${user.id}/deactivate`
        },
        'inactive': {
            action: 'activate',
            message: `Are you sure you want to activate ${user.first_name} ${user.last_name}? They will gain access to the system.`,
            endpoint: `/admin/users/${user.id}/activate`
        },
        'pending': {
            action: 'resend_verification',
            message: `Resend verification email to ${user.first_name} ${user.last_name}?`,
            endpoint: `/admin/users/${user.id}/resend-verification`
        }
    }

    const config = actions[user.status as keyof typeof actions]
    if (!config) return

    if (confirm(config.message)) {
        router.post(config.endpoint, {}, {
            preserveScroll: true,
            onSuccess: () => {
                const successMessages = {
                    activate: `${user.first_name} ${user.last_name} has been activated and can now access the system.`,
                    deactivate: `${user.first_name} ${user.last_name} has been deactivated and can no longer access the system.`,
                    resend_verification: `Verification email has been sent to ${user.first_name} ${user.last_name}.`
                }
                toast.success(successMessages[config.action as keyof typeof successMessages])
            },
            onError: (errors) => {
                console.error(`Failed to ${config.action} user:`, errors)
                toast.error(`Failed to ${config.action} user. Please try again.`)
            }
        })
    }
}

const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(`/admin/users/${userId}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User has been deleted successfully.')
            },
            onError: (errors) => {
                console.error('Failed to delete user:', errors)
                toast.error('Failed to delete user. Please try again.')
            }
        })
    }
}

// Mock data if no users provided
if (props.users.length === 0) {
    const mockUsers: User[] = [
        {
            id: 1,
            first_name: 'John',
            last_name: 'Doe',
            email: 'john.doe@company.com',
            user_type: 'hod',
            status: 'active',
            last_active: new Date().toISOString(),
            created_at: new Date().toISOString()
        },
        {
            id: 2,
            first_name: 'Jane',
            last_name: 'Smith',
            email: 'jane.smith@company.com',
            user_type: 'booking_agent',
            status: 'active',
            last_active: new Date(Date.now() - 86400000).toISOString(),
            created_at: new Date().toISOString()
        },
        {
            id: 3,
            first_name: 'Mike',
            last_name: 'Johnson',
            email: 'mike.johnson@company.com',
            user_type: 'invitee',
            status: 'pending',
            last_active: new Date(Date.now() - 172800000).toISOString(),
            created_at: new Date().toISOString()
        }
    ]
    
    // @ts-ignore - temporarily override for demo
    props.users = mockUsers
    props.stats.total_users = mockUsers.length
}
</script>