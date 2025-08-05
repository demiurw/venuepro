<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    :title="`Manage Members: ${group.name}`"
                    description="Add, remove, and manage member roles for this group"
                />
                <div class="flex items-center gap-3">
                    <Button 
                        @click="showAddMemberModal = true"
                        class="flex items-center gap-2 h-10 px-4 rounded-xl font-medium"
                    >
                        <UserPlus class="h-4 w-4" />
                        Add Members
                    </Button>
                    <Button 
                        @click="goBack"
                        variant="outline"
                        class="flex items-center gap-2 h-10 px-4 rounded-xl"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Groups
                    </Button>
                </div>
            </div>

            <!-- Status Messages -->
            <div v-if="message" class="mb-6">
                <div class="p-4 bg-success/10 border border-success/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20">
                            <CheckCircle class="h-4 w-4 text-success" />
                        </div>
                        <p class="text-body-sm text-success font-medium">{{ message }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Group Members -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                        <Users class="h-5 w-5 text-primary" />
                                        Group Members ({{ groupMembers.length }})
                                    </CardTitle>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Manage roles and access for group members
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            v-model="memberSearchQuery"
                                            placeholder="Search members..."
                                            class="pl-10 h-9 w-64 rounded-lg border-border/60"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <!-- Members List -->
                            <div v-if="filteredMembers.length > 0" class="divide-y divide-border/30">
                                <div 
                                    v-for="member in filteredMembers" 
                                    :key="member.user_id"
                                    class="p-4 hover:bg-muted/30 transition-colors"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span class="text-sm font-medium text-primary">
                                                    {{ member.user.first_name.charAt(0) }}{{ member.user.last_name.charAt(0) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground">
                                                    {{ member.user.first_name }} {{ member.user.last_name }}
                                                </p>
                                                <p class="text-sm text-muted-foreground">{{ member.user.email }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <!-- Role Badge/Selector -->
                                            <select 
                                                :value="member.role" 
                                                @change="updateMemberRole(member.user_id, ($event.target as HTMLSelectElement).value)"
                                                class="h-8 px-2 text-xs rounded-lg border border-border/60 bg-background focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                                :disabled="isUpdatingRole"
                                            >
                                                <option value="member">Member</option>
                                                <option value="manager">Manager</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            
                                            <!-- Member Info -->
                                            <div class="text-right">
                                                <p class="text-xs text-muted-foreground">
                                                    Added {{ formatDate(member.added_at) }}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    by {{ member.added_by?.first_name }} {{ member.added_by?.last_name }}
                                                </p>
                                            </div>

                                            <!-- Actions -->
                                            <Button
                                                @click="removeMember(member.user_id)"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 rounded-lg hover:bg-destructive/10 hover:text-destructive"
                                                :disabled="isUpdatingRole"
                                            >
                                                <UserMinus class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="flex flex-col items-center justify-center py-16">
                                <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                                    <Users class="h-6 w-6 text-muted-foreground" />
                                </div>
                                <h3 class="text-lg font-medium text-foreground mb-2">No members found</h3>
                                <p class="text-sm text-muted-foreground mb-6 max-w-sm text-center">
                                    {{ memberSearchQuery ? 'No members match your search' : 'This group has no members yet' }}
                                </p>
                                <Button @click="showAddMemberModal = true" class="flex items-center gap-2">
                                    <UserPlus class="h-4 w-4" />
                                    Add Members
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Group Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Group Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <span 
                                    :class="getStatusBadgeClass(group.status)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    <div :class="getStatusDotClass(group.status)" class="w-1.5 h-1.5 rounded-full"></div>
                                    {{ group.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Members</span>
                                <span class="text-sm font-medium">{{ groupMembers.length }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Created</span>
                                <span class="text-sm">{{ formatDate(group.created_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Role Distribution -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Role Distribution</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                    <span class="text-sm text-muted-foreground">Admins</span>
                                </div>
                                <span class="text-sm font-medium">{{ getRoleCount('admin') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <span class="text-sm text-muted-foreground">Managers</span>
                                </div>
                                <span class="text-sm font-medium">{{ getRoleCount('manager') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span class="text-sm text-muted-foreground">Members</span>
                                </div>
                                <span class="text-sm font-medium">{{ getRoleCount('member') }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Role Permissions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Role Permissions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-purple-700 dark:text-purple-300">Admin</h4>
                                <ul class="text-xs text-muted-foreground space-y-1">
                                    <li>• Full group management</li>
                                    <li>• Add/remove members</li>
                                    <li>• Change member roles</li>
                                    <li>• Manage group settings</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Manager</h4>
                                <ul class="text-xs text-muted-foreground space-y-1">
                                    <li>• Add members</li>
                                    <li>• View member list</li>
                                    <li>• Basic booking permissions</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-green-700 dark:text-green-300">Member</h4>
                                <ul class="text-xs text-muted-foreground space-y-1">
                                    <li>• View group information</li>
                                    <li>• Basic access only</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div v-if="showAddMemberModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <Card class="w-full max-w-2xl max-h-[80vh] overflow-hidden">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg font-semibold">Add Members to {{ group.name }}</CardTitle>
                        <Button 
                            @click="showAddMemberModal = false"
                            variant="ghost"
                            size="sm"
                            class="h-8 w-8 p-0"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Select users from your organization to add to this group
                    </p>
                </CardHeader>
                <CardContent class="overflow-y-auto">
                    <!-- Search Available Users -->
                    <div class="mb-4">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="userSearchQuery"
                                placeholder="Search available users..."
                                class="pl-10 h-11 rounded-xl border-border/60"
                            />
                        </div>
                    </div>

                    <!-- Available Users List -->
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div 
                            v-for="user in filteredAvailableUsers" 
                            :key="user.id"
                            class="flex items-center justify-between p-3 rounded-lg border border-border/30 hover:bg-muted/30 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-xs font-medium text-primary">
                                        {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-foreground">
                                        {{ user.first_name }} {{ user.last_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <select 
                                    v-model="selectedUserRoles[user.id]" 
                                    class="h-8 px-2 text-xs rounded-lg border border-border/60 bg-background focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                >
                                    <option value="member">Member</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <Button
                                    @click="addUserToGroup(user.id)"
                                    size="sm"
                                    class="h-8 px-3 text-xs"
                                    :disabled="isAddingMember"
                                >
                                    <Plus class="h-3 w-3 mr-1" />
                                    Add
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- No Available Users -->
                    <div v-if="filteredAvailableUsers.length === 0" class="text-center py-8">
                        <Users class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                        <p class="text-sm text-muted-foreground">
                            {{ userSearchQuery ? 'No users match your search' : 'All users are already members of this group' }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
    Users,
    Search,
    UserPlus,
    UserMinus,
    ArrowLeft,
    CheckCircle,
    X,
    Plus
} from 'lucide-vue-next'

interface User {
    id: number
    first_name: string
    last_name: string
    email: string
    role: string
    status: string
}

interface GroupMember {
    user_id: number
    role: 'admin' | 'manager' | 'member'
    added_at: string
    user: User
    added_by?: User
}

interface Group {
    id: number
    name: string
    description: string | null
    status: 'active' | 'inactive'
    created_at: string
    updated_at: string
}

interface Props {
    group: Group
    groupMembers?: GroupMember[]
    availableUsers?: User[]
    message?: string
}

const props = withDefaults(defineProps<Props>(), {
    groupMembers: () => [],
    availableUsers: () => [],
    message: ''
})

// State
const memberSearchQuery = ref('')
const userSearchQuery = ref('')
const showAddMemberModal = ref(false)
const isUpdatingRole = ref(false)
const isAddingMember = ref(false)
const selectedUserRoles = reactive<Record<number, string>>({})

// Initialize selected roles for available users
props.availableUsers.forEach(user => {
    selectedUserRoles[user.id] = 'member'
})

// Computed properties
const filteredMembers = computed(() => {
    if (!memberSearchQuery.value) return props.groupMembers
    const query = memberSearchQuery.value.toLowerCase()
    return props.groupMembers.filter(member => 
        member.user.first_name.toLowerCase().includes(query) ||
        member.user.last_name.toLowerCase().includes(query) ||
        member.user.email.toLowerCase().includes(query)
    )
})

const filteredAvailableUsers = computed(() => {
    const memberUserIds = new Set(props.groupMembers.map(m => m.user_id))
    let available = props.availableUsers.filter(user => !memberUserIds.has(user.id))
    
    if (userSearchQuery.value) {
        const query = userSearchQuery.value.toLowerCase()
        available = available.filter(user => 
            user.first_name.toLowerCase().includes(query) ||
            user.last_name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query)
        )
    }
    
    return available
})

// Functions
const getRoleCount = (role: string) => {
    return props.groupMembers.filter(member => member.role === role).length
}

const getStatusBadgeClass = (status: string) => {
    const classes = {
        'active': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
        'inactive': 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
    }
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
}

const getStatusDotClass = (status: string) => {
    const classes = {
        'active': 'bg-green-500',
        'inactive': 'bg-gray-400'
    }
    return classes[status as keyof typeof classes] || 'bg-gray-400'
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

const updateMemberRole = (userId: number, newRole: string) => {
    isUpdatingRole.value = true
    
    router.put(`/admin/groups/${props.group.id}/members/${userId}`, 
        { role: newRole }, 
        {
            preserveScroll: true,
            onSuccess: () => {
                // Role updated successfully
            },
            onError: (errors) => {
                console.error('Role update errors:', errors)
                alert('Failed to update member role. Please try again.')
            },
            onFinish: () => {
                isUpdatingRole.value = false
            }
        }
    )
}

const removeMember = (userId: number) => {
    const member = props.groupMembers.find(m => m.user_id === userId)
    if (!member) return
    
    const confirmMessage = `Are you sure you want to remove ${member.user.first_name} ${member.user.last_name} from this group?`
    
    if (confirm(confirmMessage)) {
        router.delete(`/admin/groups/${props.group.id}/members/${userId}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Member removed successfully
            },
            onError: () => {
                alert('Failed to remove member. Please try again.')
            }
        })
    }
}

const addUserToGroup = (userId: number) => {
    const role = selectedUserRoles[userId] || 'member'
    isAddingMember.value = true
    
    router.post(`/admin/groups/${props.group.id}/members`, 
        { 
            user_id: userId,
            role: role
        }, 
        {
            preserveScroll: true,
            onSuccess: () => {
                // Reset search and close modal if this was the last user
                userSearchQuery.value = ''
                if (filteredAvailableUsers.value.length <= 1) {
                    showAddMemberModal.value = false
                }
            },
            onError: (errors) => {
                console.error('Add member errors:', errors)
                alert('Failed to add member. Please try again.')
            },
            onFinish: () => {
                isAddingMember.value = false
            }
        }
    )
}

const goBack = () => {
    router.visit('/admin/groups')
}

// Mock data for demonstration
if (props.groupMembers.length === 0) {
    const mockMembers: GroupMember[] = [
        {
            user_id: 1,
            role: 'admin',
            added_at: new Date().toISOString(),
            user: {
                id: 1,
                first_name: 'John',
                last_name: 'Doe',
                email: 'john.doe@company.com',
                role: 'Head of Department',
                status: 'active'
            },
            added_by: {
                id: 0,
                first_name: 'System',
                last_name: 'Admin',
                email: 'admin@venuepro.com',
                role: 'Admin',
                status: 'active'
            }
        },
        {
            user_id: 2,
            role: 'manager',
            added_at: new Date(Date.now() - 86400000).toISOString(),
            user: {
                id: 2,
                first_name: 'Jane',
                last_name: 'Smith',
                email: 'jane.smith@company.com',
                role: 'Booking Agent',
                status: 'active'
            },
            added_by: {
                id: 1,
                first_name: 'John',
                last_name: 'Doe',
                email: 'john.doe@company.com',
                role: 'Head of Department',
                status: 'active'
            }
        }
    ]
    
    const mockAvailableUsers: User[] = [
        {
            id: 3,
            first_name: 'Mike',
            last_name: 'Johnson',
            email: 'mike.johnson@company.com',
            role: 'Invitee',
            status: 'active'
        },
        {
            id: 4,
            first_name: 'Sarah',
            last_name: 'Wilson',
            email: 'sarah.wilson@company.com',
            role: 'Booking Agent',
            status: 'active'
        }
    ]
    
    // @ts-ignore - temporarily override for demo
    props.groupMembers = mockMembers
    props.availableUsers = mockAvailableUsers
    
    // Initialize roles for mock users
    mockAvailableUsers.forEach(user => {
        selectedUserRoles[user.id] = 'member'
    })
}
</script>