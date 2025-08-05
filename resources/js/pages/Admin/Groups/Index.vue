<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    title="Group Management" 
                    description="Manage groups and team organizations for your VenuePro workspace"
                />
                <Button 
                    @click="createGroup"
                    class="flex items-center gap-2 h-10 px-4 rounded-xl font-medium transition-smooth"
                >
                    <Users class="h-4 w-4" />
                    Add New Group
                </Button>
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search groups by name or description..."
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
                    </select>
                </div>
            </div>

            <!-- Groups Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg font-semibold">Groups ({{ filteredGroups.length }})</CardTitle>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Users class="h-4 w-4" />
                            {{ stats.total_groups }} total groups
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border/50">
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Group</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Description</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Members</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Status</th>
                                    <th class="text-left p-4 text-sm font-medium text-muted-foreground">Created</th>
                                    <th class="text-right p-4 text-sm font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="group in paginatedGroups" 
                                    :key="group.id"
                                    class="border-b border-border/30 hover:bg-muted/30 transition-colors"
                                >
                                    <!-- Group Info -->
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                <Users class="h-4 w-4 text-primary" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground">{{ group.name }}</p>
                                                <p class="text-sm text-muted-foreground">ID: {{ group.id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Description -->
                                    <td class="p-4">
                                        <p class="text-sm text-foreground max-w-xs truncate">
                                            {{ group.description || 'No description' }}
                                        </p>
                                    </td>
                                    
                                    <!-- Member Count -->
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-foreground">{{ group.member_count }}</span>
                                            <span class="text-xs text-muted-foreground">members</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="p-4">
                                        <span 
                                            :class="getStatusBadgeClass(group.status)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                        >
                                            <div :class="getStatusDotClass(group.status)" class="w-1.5 h-1.5 rounded-full"></div>
                                            {{ group.status }}
                                        </span>
                                    </td>
                                    
                                    <!-- Created Date -->
                                    <td class="p-4">
                                        <p class="text-sm text-muted-foreground">{{ formatDate(group.created_at) }}</p>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="p-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button
                                                @click="manageMembers(group.id)"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 rounded-lg hover:bg-blue/10 hover:text-blue"
                                            >
                                                <UserCog class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                @click="editGroup(group.id)"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 rounded-lg hover:bg-primary/10"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                @click="deleteGroup(group.id)"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 rounded-lg hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredGroups.length === 0" class="flex flex-col items-center justify-center py-16">
                        <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                            <Users class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-medium text-foreground mb-2">No groups found</h3>
                        <p class="text-sm text-muted-foreground mb-6 max-w-sm text-center">
                            {{ searchQuery || statusFilter ? 'Try adjusting your search or filters' : 'Get started by creating your first group' }}
                        </p>
                        <Button @click="createGroup" class="flex items-center gap-2">
                            <Users class="h-4 w-4" />
                            Add New Group
                        </Button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="filteredGroups.length > 0" class="flex items-center justify-between p-4 border-t border-border/50">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ startIndex + 1 }}-{{ Math.min(endIndex, filteredGroups.length) }} of {{ filteredGroups.length }} groups
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
    </AppLayout>
</template>

<script setup lang="ts">
import { withDefaults, computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
    Users,
    Search,
    Edit,
    Trash2,
    UserCog,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next'

interface Group {
    id: number
    name: string
    description: string | null
    status: 'active' | 'inactive'
    member_count: number
    created_at: string
    updated_at: string
}

interface Props {
    groups?: Group[]
    stats?: {
        total_groups: number
        active_groups: number
        total_members: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    groups: () => [],
    stats: () => ({
        total_groups: 0,
        active_groups: 0,
        total_members: 0
    })
})

// Search and filter state
const searchQuery = ref('')
const statusFilter = ref('')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = 10

// Computed properties
const filteredGroups = computed(() => {
    let filtered = props.groups

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(group => 
            group.name.toLowerCase().includes(query) ||
            (group.description && group.description.toLowerCase().includes(query))
        )
    }

    // Status filter
    if (statusFilter.value) {
        filtered = filtered.filter(group => group.status === statusFilter.value)
    }

    return filtered
})

const totalPages = computed(() => Math.ceil(filteredGroups.value.length / itemsPerPage))

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => startIndex.value + itemsPerPage)

const paginatedGroups = computed(() => {
    const groups = filteredGroups.value
    if (!Array.isArray(groups)) return []
    return groups.slice(startIndex.value, endIndex.value)
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
const createGroup = () => {
    router.visit('/admin/groups/create')
}

const editGroup = (groupId: number) => {
    router.visit(`/admin/groups/${groupId}/edit`)
}

const manageMembers = (groupId: number) => {
    router.visit(`/admin/groups/${groupId}/members`)
}

const deleteGroup = (groupId: number) => {
    if (confirm('Are you sure you want to delete this group? This action cannot be undone and will remove all members from the group.')) {
        router.delete(`/admin/groups/${groupId}`, {
            preserveScroll: true
        })
    }
}

// Mock data if no groups provided
if (props.groups.length === 0) {
    const mockGroups: Group[] = [
        {
            id: 1,
            name: 'Marketing Team',
            description: 'Marketing department group for campaign coordination',
            status: 'active',
            member_count: 8,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        },
        {
            id: 2,
            name: 'Development Team',
            description: 'Software development and engineering team',
            status: 'active',
            member_count: 12,
            created_at: new Date(Date.now() - 86400000).toISOString(),
            updated_at: new Date().toISOString()
        },
        {
            id: 3,
            name: 'Project Alpha',
            description: 'Special project team for Alpha initiative',
            status: 'inactive',
            member_count: 5,
            created_at: new Date(Date.now() - 172800000).toISOString(),
            updated_at: new Date().toISOString()
        },
        {
            id: 4,
            name: 'Executive Team',
            description: 'Senior leadership and executive members',
            status: 'active',
            member_count: 6,
            created_at: new Date(Date.now() - 259200000).toISOString(),
            updated_at: new Date().toISOString()
        }
    ]
    
    // @ts-ignore - temporarily override for demo
    props.groups = mockGroups
    props.stats.total_groups = mockGroups.length
    props.stats.active_groups = mockGroups.filter(g => g.status === 'active').length
    props.stats.total_members = mockGroups.reduce((sum, g) => sum + g.member_count, 0)
}
</script>