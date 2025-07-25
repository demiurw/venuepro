<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading
                title="Booking Agent Dashboard"
                description="Manage bookings, clients, and scheduling operations"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    New Booking
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    View Calendar
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <UserPlus class="h-4 w-4" />
                    Add Client
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <MessageSquare class="h-4 w-4" />
                    Client Requests
                </Button>
            </div>

            <!-- Agent Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Today's Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Today's Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.todays_bookings || 8 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            +2 from yesterday
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Clients -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Active Clients</CardTitle>
                        <Users class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.active_clients || 24 }}</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            3 new this week
                        </p>
                    </CardContent>
                </Card>

                <!-- Room Utilization -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Room Utilization</CardTitle>
                        <Building class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.room_utilization || 87 }}%</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            High efficiency
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Requests -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Pending Requests</CardTitle>
                        <AlertCircle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_requests || 6 }}</div>
                        <p class="text-xs text-orange-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            Needs response
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Today's Schedule -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Today's Schedule</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="booking in todaysSchedule" :key="booking.id"
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${booking.bgColor} rounded-full flex items-center justify-center`">
                                        <Clock class="h-5 w-5" :class="booking.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ booking.time }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.room }} • {{ booking.client }}</p>
                                        <p class="text-xs text-muted-foreground">{{ booking.type }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${booking.statusClass}`">
                                        {{ booking.status }}
                                    </span>
                                    <div class="mt-1">
                                        <Button size="sm" variant="ghost" class="h-6 px-2 text-xs">
                                            Details
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View Full Calendar
                                <Calendar class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Client Requests -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Recent Client Requests</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="request in clientRequests" :key="request.id"
                                 class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${request.bgColor} rounded-lg flex items-center justify-center`">
                                        <span :class="`${request.textColor} font-semibold text-sm`">{{ request.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ request.client }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.room }} • {{ request.date }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.duration }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-1">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${request.priorityClass}`">
                                        {{ request.priority }}
                                    </span>
                                    <Button size="sm" variant="outline" class="h-6 px-3 text-xs">
                                        Process
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All Requests
                                <MessageSquare class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Performance Analytics -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">Agent Performance Analytics</CardTitle>
                    <p class="text-sm text-muted-foreground">Your booking management metrics</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Booking Success Rate -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">94%</div>
                            <p class="text-sm text-muted-foreground mt-1">Success Rate</p>
                            <p class="text-xs text-green-600 mt-1">+2% this month</p>
                        </div>

                        <!-- Average Response Time -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">12m</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Response</p>
                            <p class="text-xs text-green-600 mt-1">-3m improvement</p>
                        </div>

                        <!-- Client Satisfaction -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">4.9</div>
                            <p class="text-sm text-muted-foreground mt-1">Client Rating</p>
                            <p class="text-xs text-green-600 mt-1">Out of 5.0</p>
                        </div>

                        <!-- Bookings This Month -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">127</div>
                            <p class="text-sm text-muted-foreground mt-1">Monthly Total</p>
                            <p class="text-xs text-green-600 mt-1">+15% growth</p>
                        </div>
                    </div>

                    <!-- Quick Client Management -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="font-medium text-sm mb-4">Quick Client Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-blue-600">8</div>
                                <p class="text-xs text-muted-foreground">VIP Clients</p>
                            </div>
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-green-600">16</div>
                                <p class="text-xs text-muted-foreground">Regular Clients</p>
                            </div>
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-orange-600">3</div>
                                <p class="text-xs text-muted-foreground">New Clients</p>
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
    Plus,
    Calendar,
    UserPlus,
    MessageSquare,
    TrendingUp,
    Users,
    Clock,
    Building,
    AlertCircle
} from 'lucide-vue-next'

interface Props {
    user: any
    stats?: {
        todays_bookings: number
        active_clients: number
        room_utilization: number
        pending_requests: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        todays_bookings: 8,
        active_clients: 24,
        room_utilization: 87,
        pending_requests: 6
    })
})

// Mock data for today's schedule
const todaysSchedule = [
    {
        id: 1,
        time: '9:00 AM - 10:30 AM',
        room: 'Conference A',
        client: 'Tech Corp',
        type: 'Board Meeting',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 2,
        time: '11:00 AM - 12:00 PM',
        room: 'Meeting B',
        client: 'Design Studio',
        type: 'Client Review',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 3,
        time: '2:00 PM - 3:30 PM',
        room: 'Training Room',
        client: 'Marketing Inc',
        type: 'Workshop',
        status: 'Pending',
        statusClass: 'bg-yellow-100 text-yellow-700',
        bgColor: 'bg-yellow-100',
        textColor: 'text-yellow-600'
    },
    {
        id: 4,
        time: '4:00 PM - 5:00 PM',
        room: 'Board Room',
        client: 'Finance Ltd',
        type: 'Presentation',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    }
]

// Mock data for client requests
const clientRequests = [
    {
        id: 1,
        client: 'Global Enterprises',
        room: 'Conference A',
        date: 'Tomorrow',
        duration: '2 hours',
        priority: 'High',
        priorityClass: 'bg-red-100 text-red-700',
        initials: 'GE',
        bgColor: 'bg-red-100',
        textColor: 'text-red-600'
    },
    {
        id: 2,
        client: 'Creative Agency',
        room: 'Meeting B',
        date: 'Jan 28',
        duration: '1.5 hours',
        priority: 'Medium',
        priorityClass: 'bg-yellow-100 text-yellow-700',
        initials: 'CA',
        bgColor: 'bg-yellow-100',
        textColor: 'text-yellow-600'
    },
    {
        id: 3,
        client: 'StartupXYZ',
        room: 'Training Room',
        date: 'Jan 29',
        duration: '3 hours',
        priority: 'Low',
        priorityClass: 'bg-green-100 text-green-700',
        initials: 'SX',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    }
]
</script>
