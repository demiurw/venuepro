<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="External User Dashboard" 
                description="Submit booking requests and manage your access"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    Request Booking
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Clock class="h-4 w-4" />
                    My Requests
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    Approved Bookings
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <MessageSquare class="h-4 w-4" />
                    Contact Support
                </Button>
            </div>

            <!-- External User Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Pending Requests -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Pending Requests</CardTitle>
                        <Clock class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_requests || 3 }}</div>
                        <p class="text-xs text-orange-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            Awaiting approval
                        </p>
                    </CardContent>
                </Card>

                <!-- Approved Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Approved Bookings</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.approved_bookings || 8 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            2 upcoming
                        </p>
                    </CardContent>
                </Card>

                <!-- Request Success Rate -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Success Rate</CardTitle>
                        <Star class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.success_rate || 87 }}%</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            Good standing
                        </p>
                    </CardContent>
                </Card>

                <!-- Account Status -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Account Status</CardTitle>
                        <Shield class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">Active</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <CheckCircle class="h-3 w-3 mr-1" />
                            Verified
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Requests -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Recent Requests</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="request in recentRequests" :key="request.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${request.bgColor} rounded-full flex items-center justify-center`">
                                        <Calendar class="h-5 w-5" :class="request.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ request.room }}</p>
                                        <p class="text-sm text-muted-foreground">{{ request.date }} • {{ request.time }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.purpose }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${request.statusClass}`">
                                        {{ request.status }}
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
                                View All Requests
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Booking Guidelines -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Booking Guidelines</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-start space-x-3">
                                    <Info class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <h4 class="font-medium text-blue-900 text-sm">Request Process</h4>
                                        <p class="text-xs text-blue-700 mt-1">All booking requests require approval from the facility manager. Please allow 24-48 hours for processing.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-start space-x-3">
                                    <CheckCircle class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <h4 class="font-medium text-green-900 text-sm">Required Information</h4>
                                        <p class="text-xs text-green-700 mt-1">Include purpose, expected attendees, and any special requirements in your request.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex items-start space-x-3">
                                    <AlertTriangle class="h-5 w-5 text-yellow-600 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <h4 class="font-medium text-yellow-900 text-sm">Cancellation Policy</h4>
                                        <p class="text-xs text-yellow-700 mt-1">Please cancel at least 4 hours in advance to avoid restrictions on future bookings.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Request History & Analytics -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">Request History & Analytics</CardTitle>
                    <p class="text-sm text-muted-foreground">Your booking patterns and history</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total Requests -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ stats.total_requests || 23 }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Total Requests</p>
                            <p class="text-xs text-blue-600 mt-1">Since joining</p>
                        </div>

                        <!-- Average Processing Time -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">28h</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Processing</p>
                            <p class="text-xs text-green-600 mt-1">Faster than average</p>
                        </div>

                        <!-- Most Requested Room -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">Conf. A</div>
                            <p class="text-sm text-muted-foreground mt-1">Most Requested</p>
                            <p class="text-xs text-purple-600 mt-1">42% of requests</p>
                        </div>

                        <!-- Compliance Score -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">95%</div>
                            <p class="text-sm text-muted-foreground mt-1">Compliance Score</p>
                            <p class="text-xs text-green-600 mt-1">Excellent record</p>
                        </div>
                    </div>

                    <!-- Support Information -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="font-medium text-sm mb-4">Need Help?</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 border rounded-lg">
                                <div class="flex items-center space-x-3 mb-2">
                                    <MessageSquare class="h-5 w-5 text-blue-600" />
                                    <h5 class="font-medium text-sm">Contact Support</h5>
                                </div>
                                <p class="text-xs text-muted-foreground mb-3">Get help with booking requests or account issues</p>
                                <Button size="sm" variant="outline" class="w-full">
                                    Send Message
                                </Button>
                            </div>
                            <div class="p-4 border rounded-lg">
                                <div class="flex items-center space-x-3 mb-2">
                                    <FileText class="h-5 w-5 text-green-600" />
                                    <h5 class="font-medium text-sm">Facility Guide</h5>
                                </div>
                                <p class="text-xs text-muted-foreground mb-3">Learn about available rooms and amenities</p>
                                <Button size="sm" variant="outline" class="w-full">
                                    View Guide
                                </Button>
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
    Clock,
    Calendar,
    MessageSquare,
    CheckCircle,
    TrendingUp,
    Star,
    Shield,
    Info,
    AlertTriangle,
    ChevronRight,
    FileText
} from 'lucide-vue-next'

interface Props {
    user: any
    stats?: {
        pending_requests: number
        approved_bookings: number
        success_rate: number
        total_requests: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        pending_requests: 3,
        approved_bookings: 8,
        success_rate: 87,
        total_requests: 23
    })
})

// Mock data for recent requests
const recentRequests = [
    {
        id: 1,
        room: 'Conference Room A',
        date: 'Jan 28',
        time: '2:00 PM - 4:00 PM',
        purpose: 'Client Presentation',
        status: 'Approved',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 2,
        room: 'Meeting Room B',
        date: 'Jan 26',
        time: '10:00 AM - 12:00 PM',
        purpose: 'Team Workshop',
        status: 'Pending',
        statusClass: 'bg-yellow-100 text-yellow-700',
        bgColor: 'bg-yellow-100',
        textColor: 'text-yellow-600'
    },
    {
        id: 3,
        room: 'Training Room',
        date: 'Jan 24',
        time: '1:00 PM - 3:00 PM',
        purpose: 'Interview Session',
        status: 'Approved',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 4,
        room: 'Board Room',
        date: 'Jan 22',
        time: '9:00 AM - 11:00 AM',
        purpose: 'Quarterly Review',
        status: 'Denied',
        statusClass: 'bg-red-100 text-red-700',
        bgColor: 'bg-red-100',
        textColor: 'text-red-600'
    }
]
</script>