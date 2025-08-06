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
                        <CardTitle class="text-lg font-semibold">Users ({{ users.total }})</CardTitle>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Users class="h-4 w-4" />
                            {{ roleStats.total }} total users
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
                                    v-for="user in users.data" 
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
                                        <StatusBadge :status="user.status" />
                                    </td>
                                    
                                    <!-- Last Active -->
                                    <td class="p-4">
                                        <p class="text-sm text-muted-foreground">{{ formatDate(user.last_active) }}</p>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="p-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <span class="sr-only">Open menu</span>
                                                    <MoreVertical class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="editUser(user.id)">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    <span>Edit</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="user.status === 'inactive' || user.status === 'pending'" @click="initiateStatusChange(user, 'active')">
                                                    <UserCheck class="mr-2 h-4 w-4" />
                                                    <span>Activate</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="user.status === 'active'" @click="initiateStatusChange(user, 'inactive')">
                                                    <UserX class="mr-2 h-4 w-4" />
                                                    <span>Deactivate</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deleteUser(user.id)" class="text-red-600">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    <span>Delete</span>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
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
                    <div v-if="users.links.length > 3" class="flex items-center justify-between p-4 border-t border-border/50">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                        </p>
                        <div class="flex items-center gap-2">
                            <Button
                                v-for="(link, key) in users.links"
                                :key="key"
                                :href="link.url"
                                v-html="link.label"
                                :disabled="!link.url"
                                :class="{ 'bg-primary text-primary-foreground': link.active }"
                                variant="outline"
                                size="sm"
                                class="h-8 px-3 rounded-lg"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- OTP Modal -->
        <Dialog :open="isOtpModalVisible" @update:open="isOtpModalVisible = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Confirm Action</DialogTitle>
                    <DialogDescription>
                        An OTP has been sent to your email. Please enter it below to confirm the status change for {{ targetUserForStatusChange?.first_name }} {{ targetUserForStatusChange?.last_name }}.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <Input
                        v-model="otpCode"
                        placeholder="Enter 6-digit OTP"
                        class="col-span-3"
                        @keyup.enter="confirmStatusChange"
                    />
                    <p v-if="otpError" class="text-red-500 text-sm">{{ otpError }}</p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isOtpModalVisible = false">Cancel</Button>
                    <Button @click="confirmStatusChange">Confirm</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import {
    UserPlus,
    Search,
    Users,
    Edit,
    Trash2,
    UserCheck,
    UserX,
    Clock,
    MoreVertical
} from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'vue-sonner'

// OTP Modal State
const isOtpModalVisible = ref(false)
const otpCode = ref('')
const targetUserForStatusChange = ref<User | null>(null)
const newStatusForChange = ref<string | null>(null)
const otpError = ref<string | null>(null)


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

interface PaginatedUsers {
    data: User[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number;
    to: number;
    total: number;
}

interface Props {
    users: PaginatedUsers;
    filters: {
        search: string;
        role: string;
        status: string;
    };
    allowedRoles: string[];
    roleStats: {
        total?: number;
        hod?: number;
        booking_agent?: number;
        invitee?: number;
    };
}

const props = defineProps<Props>();

// Search and filter state
const searchQuery = ref(props.filters.search)
const statusFilter = ref(props.filters.status)
const roleFilter = ref(props.filters.role)

watch([searchQuery, statusFilter, roleFilter], () => {
    router.get(
        route('admin.users.index'),
        {
            search: searchQuery.value,
            status: statusFilter.value,
            role: roleFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    )
});


// Computed properties
const filteredUsers = computed(() => {
    return props.users.data;
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

const initiateStatusChange = async (user: User, newStatus: string) => {
    targetUserForStatusChange.value = user;
    newStatusForChange.value = newStatus;
    otpCode.value = '';
    otpError.value = null;

    try {
        await router.post(route('admin.users.send-action-otp'), { action: 'user_status_change' }, {
            preserveScroll: true,
            onSuccess: () => {
                isOtpModalVisible.value = true;
                toast.info('An OTP has been sent to your email.');
            },
            onError: (errors) => {
                toast.error('Failed to send OTP', { description: Object.values(errors).join('\n') });
            }
        });
    } catch (e) {
        toast.error('An unexpected error occurred while sending OTP.');
    }
};

const confirmStatusChange = () => {
    if (!targetUserForStatusChange.value || !newStatusForChange.value) return;

    const user = targetUserForStatusChange.value;
    const endpoint = newStatusForChange.value === 'active'
        ? route('admin.users.reactivate', user.id)
        : `/admin/users/${user.id}`; // Uses destroy method for deactivation

    const method = newStatusForChange.value === 'active' ? 'post' : 'delete';

    router.visit(endpoint, {
        method: method,
        data: { otp: otpCode.value },
        preserveScroll: true,
        onSuccess: () => {
            isOtpModalVisible.value = false;
            toast.success(`User ${user.first_name} has been ${newStatusForChange.value}.`);
        },
        onError: (errors) => {
            otpError.value = errors.otp || 'An unknown error occurred.';
        }
    });
};


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


</script>