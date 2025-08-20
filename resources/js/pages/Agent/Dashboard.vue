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
                        <div class="text-3xl font-bold text-foreground">{{ stats.todays_bookings }}</div>
                        <p v-if="stats.bookings_change" class="text-xs flex items-center mt-1" :class="stats.bookings_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            <TrendingUp v-if="stats.bookings_change >= 0" class="h-3 w-3 mr-1" />
                            <TrendingDown v-else class="h-3 w-3 mr-1" />
                            {{ stats.bookings_change > 0 ? '+' : '' }}{{ stats.bookings_change }} from yesterday
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.active_clients }}</div>
                        <p v-if="stats.new_clients_weekly" class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            {{ stats.new_clients_weekly }} new this week
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.room_utilization }}%</div>
                        <p v-if="stats.utilization_status" class="text-xs flex items-center mt-1" :class="stats.utilization_status === 'High efficiency' ? 'text-green-600' : 'text-orange-600'">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            {{ stats.utilization_status }}
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_requests }}</div>
                        <p v-if="stats.pending_requests > 0" class="text-xs text-orange-600 flex items-center mt-1">
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
                        <div class="space-y-4" v-if="todaysSchedule && todaysSchedule.length > 0">
                            <div v-for="booking in todaysSchedule" :key="booking.id"
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${booking.bg_color} rounded-full flex items-center justify-center`">
                                        <Clock class="h-5 w-5" :class="booking.text_color" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ booking.formatted_time }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.room_name }} • {{ booking.client_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ booking.booking_type }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${booking.status_class}`">
                                        {{ booking.status }}
                                    </span>
                                    <div class="mt-1">
                                        <Button size="sm" variant="ghost" class="h-6 px-2 text-xs" @click="viewBookingDetails(booking.id)">
                                            Details
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Calendar class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No bookings scheduled for today</p>
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
                        <div class="space-y-4" v-if="clientRequests && clientRequests.length > 0">
                            <div v-for="request in clientRequests" :key="request.id"
                                 class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${request.bg_color} rounded-lg flex items-center justify-center`">
                                        <span :class="`${request.text_color} font-semibold text-sm`">{{ request.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ request.client_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.room_name }} • {{ request.formatted_date }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.duration }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-1">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${request.priority_class}`">
                                        {{ request.priority }}
                                    </span>
                                    <Button size="sm" variant="outline" class="h-6 px-3 text-xs" @click="processRequest(request.id)">
                                        Process
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <MessageSquare class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No pending client requests</p>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" v-if="performance">
                        <!-- Booking Success Rate -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ performance.success_rate || 0 }}%</div>
                            <p class="text-sm text-muted-foreground mt-1">Success Rate</p>
                            <p v-if="performance.success_rate_change" class="text-xs mt-1" :class="performance.success_rate_change >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ performance.success_rate_change > 0 ? '+' : '' }}{{ performance.success_rate_change }}% this month
                            </p>
                        </div>

                        <!-- Average Response Time -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ performance.avg_response_time || 'N/A' }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Response</p>
                            <p v-if="performance.response_improvement" class="text-xs text-green-600 mt-1">{{ performance.response_improvement }}</p>
                        </div>

                        <!-- Client Satisfaction -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ performance.client_rating || 'N/A' }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Client Rating</p>
                            <p v-if="performance.rating_max" class="text-xs text-green-600 mt-1">Out of {{ performance.rating_max }}</p>
                        </div>

                        <!-- Bookings This Month -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">{{ performance.monthly_bookings || 0 }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Monthly Total</p>
                            <p v-if="performance.monthly_growth" class="text-xs mt-1" :class="performance.monthly_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ performance.monthly_growth > 0 ? '+' : '' }}{{ performance.monthly_growth }}% growth
                            </p>
                        </div>
                    </div>

                    <!-- Quick Client Management -->
                    <div class="mt-6 pt-6 border-t" v-if="clientSummary">
                        <h4 class="font-medium text-sm mb-4">Quick Client Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-blue-600">{{ clientSummary.vip_clients || 0 }}</div>
                                <p class="text-xs text-muted-foreground">VIP Clients</p>
                            </div>
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-green-600">{{ clientSummary.regular_clients || 0 }}</div>
                                <p class="text-xs text-muted-foreground">Regular Clients</p>
                            </div>
                            <div class="p-3 border rounded-lg text-center">
                                <div class="text-lg font-bold text-orange-600">{{ clientSummary.new_clients || 0 }}</div>
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
    stats: {
        todays_bookings: number
        bookings_change?: number
        active_clients: number
        new_clients_weekly?: number
        room_utilization: number
        utilization_status?: string
        pending_requests: number
    }
    todaysSchedule?: Array<{
        id: number
        formatted_time: string
        room_name: string
        client_name: string
        booking_type: string
        status: string
        status_class: string
        bg_color: string
        text_color: string
    }>
    clientRequests?: Array<{
        id: number
        client_name: string
        room_name: string
        formatted_date: string
        duration: string
        priority: string
        priority_class: string
        initials: string
        bg_color: string
        text_color: string
    }>
    performance?: {
        success_rate: number
        success_rate_change?: number
        avg_response_time: string
        response_improvement?: string
        client_rating: number | string
        rating_max?: number
        monthly_bookings: number
        monthly_growth?: number
    }
    clientSummary?: {
        vip_clients: number
        regular_clients: number
        new_clients: number
    }
}

const props = defineProps<Props>()

// Methods for handling actions
const viewBookingDetails = (bookingId: number) => {
    console.log('Viewing booking details:', bookingId)
}

const processRequest = (requestId: number) => {
    console.log('Processing request:', requestId)
}
</script>
