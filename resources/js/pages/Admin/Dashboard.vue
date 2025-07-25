<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="System Admin Dashboard" 
                description="Company management overview and administrative tools"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <UserPlus class="h-4 w-4" />
                    Add User
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <MapPin class="h-4 w-4" />
                    Add Room
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Settings class="h-4 w-4" />
                    Company Settings
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <FileText class="h-4 w-4" />
                    Reports
                </Button>
            </div>

            <!-- Company Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Users -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_users || 45 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            +5 this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Active Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.active_bookings || 23 }}</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            12 today
                        </p>
                    </CardContent>
                </Card>

                <!-- Available Rooms -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Available Rooms</CardTitle>
                        <Building class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_rooms || 18 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <MapPin class="h-3 w-3 mr-1" />
                            85% utilization
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Approvals -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Pending Approvals</CardTitle>
                        <AlertCircle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_approvals || 7 }}</div>
                        <p class="text-xs text-orange-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            Needs attention
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Bookings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Recent Bookings</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="booking in recentBookings" :key="booking.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${booking.bgColor} rounded-full flex items-center justify-center`">
                                        <Calendar class="h-5 w-5" :class="booking.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ booking.room }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.user }} • {{ booking.time }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-foreground">{{ booking.date }}</p>
                                    <span :class="`text-xs px-2 py-1 rounded-full ${booking.statusClass}`">
                                        {{ booking.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All Bookings
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Room Utilization -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Room Utilization</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="room in roomUtilization" :key="room.id" 
                                 class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div :class="`h-8 w-8 ${room.bgColor} rounded-lg flex items-center justify-center`">
                                        <MapPin class="h-4 w-4" :class="room.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ room.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ room.building }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium">{{ room.utilization }}%</p>
                                    <div class="w-16 h-2 bg-secondary rounded-full mt-1">
                                        <div :class="`h-full rounded-full ${room.barColor}`" 
                                             :style="`width: ${room.utilization}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                Manage Rooms
                                <Settings class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- User Management Overview -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">User Management Overview</CardTitle>
                    <p class="text-sm text-muted-foreground">Recent user activity and management actions</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- User Statistics -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-sm">User Statistics</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">Active Users</span>
                                    <span class="text-sm font-medium">{{ stats.active_users || 42 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">Pending Users</span>
                                    <span class="text-sm font-medium text-orange-600">{{ stats.pending_users || 3 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">Total Groups</span>
                                    <span class="text-sm font-medium">{{ stats.total_groups || 8 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Actions -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-sm">Recent Actions</h4>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                    <span class="text-muted-foreground">User activated: John Doe</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-muted-foreground">Room added: Conference B</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 bg-purple-500 rounded-full"></div>
                                    <span class="text-muted-foreground">Group created: Marketing</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-sm">This Month</h4>
                            <div class="space-y-2">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">97%</div>
                                    <p class="text-xs text-muted-foreground">User Satisfaction</p>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">145</div>
                                    <p class="text-xs text-muted-foreground">Total Bookings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { withDefaults } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import {
    UserPlus,
    MapPin,
    Settings,
    FileText,
    TrendingUp,
    Users,
    Calendar,
    Clock,
    Building,
    AlertCircle,
    ChevronRight
} from 'lucide-vue-next'

interface Props {
    user: any
    stats?: {
        total_users: number
        active_users: number
        pending_users: number
        active_bookings: number
        total_rooms: number
        total_groups: number
        pending_approvals: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_users: 45,
        active_users: 42,
        pending_users: 3,
        active_bookings: 23,
        total_rooms: 18,
        total_groups: 8,
        pending_approvals: 7
    })
})

// Mock data for recent bookings
const recentBookings = [
    {
        id: 1,
        room: 'Conference Room A',
        user: 'Sarah Johnson',
        time: '2:00 PM - 3:30 PM',
        date: 'Today',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 2,
        room: 'Meeting Room B',
        user: 'Mike Chen',
        time: '10:00 AM - 11:00 AM',
        date: 'Tomorrow',
        status: 'Pending',
        statusClass: 'bg-yellow-100 text-yellow-700',
        bgColor: 'bg-yellow-100',
        textColor: 'text-yellow-600'
    },
    {
        id: 3,
        room: 'Board Room',
        user: 'Emily Davis',
        time: '9:00 AM - 10:30 AM',
        date: 'Jan 27',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    }
]

// Mock data for room utilization
const roomUtilization = [
    {
        id: 1,
        name: 'Conference A',
        building: 'Main Building',
        utilization: 85,
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600',
        barColor: 'bg-blue-500'
    },
    {
        id: 2,
        name: 'Meeting B',
        building: 'Main Building',
        utilization: 92,
        bgColor: 'bg-green-100',
        textColor: 'text-green-600',
        barColor: 'bg-green-500'
    },
    {
        id: 3,
        name: 'Board Room',
        building: 'Executive Wing',
        utilization: 67,
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600',
        barColor: 'bg-purple-500'
    },
    {
        id: 4,
        name: 'Training Room',
        building: 'Annex',
        utilization: 45,
        bgColor: 'bg-orange-100',
        textColor: 'text-orange-600',
        barColor: 'bg-orange-500'
    }
]
</script>