<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

// Placeholder data
const statsData = {
    upcomingBookings: 5,
    activeTeamMembers: 12,
    availableRooms: 8
}

const teamPerformance = [
    { metric: 'Meeting Attendance', value: '94%', color: 'bg-green-400' },
    { metric: 'Room Utilization', value: '87%', color: 'bg-blue-400' },
    { metric: 'Booking Efficiency', value: '91%', color: 'bg-yellow-400' },
    { metric: 'Resource Usage', value: '78%', color: 'bg-purple-400' }
]

const recentActivity = [
    {
        id: 1,
        name: 'John Doe',
        action: 'Booked Conference Room A',
        time: '2 hours ago',
        initials: 'JD',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 2,
        name: 'Sarah Miller',
        action: 'Completed training session',
        time: '4 hours ago',
        initials: 'SM',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 3,
        name: 'Robert Johnson',
        action: 'Updated project timeline',
        time: '6 hours ago',
        initials: 'RJ',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    },
    {
        id: 4,
        name: 'Emily Chen',
        action: 'Joined team meeting',
        time: '1 day ago',
        initials: 'EM',
        bgColor: 'bg-orange-100',
        textColor: 'text-orange-600'
    }
]
</script>
<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading>Manager Dashboard</Heading>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Your Upcoming Bookings Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Your Upcoming Bookings</CardTitle>
                        <div class="h-4 w-4 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">5</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            Next: Tomorrow 9:00 AM
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Team Members Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Active Team Members</CardTitle>
                        <div class="h-4 w-4 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">12</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            8 online now
                        </p>
                    </CardContent>
                </Card>

                <!-- Available Rooms Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Available Rooms</CardTitle>
                        <div class="h-4 w-4 text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9,22 9,12 15,12 15,22"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">8</div>
                        <p class="text-xs text-purple-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Right now
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-gray-900">Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <Button
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Add New User</span>
                        </Button>

                        <Button
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v3m0-3h3m-3 0h-3"/>
                            </svg>
                            <span>Create a Booking</span>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Team Activity Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Team Performance -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-gray-900">Team Performance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-2 w-2 bg-green-400 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Meeting Attendance</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">94%</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Room Utilization</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">87%</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-2 w-2 bg-yellow-400 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Booking Efficiency</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">91%</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-2 w-2 bg-purple-400 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Resource Usage</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">78%</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Team Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-gray-900">Recent Team Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-xs">JD</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">John Doe</p>
                                    <p class="text-xs text-gray-500">Booked Conference Room A</p>
                                    <p class="text-xs text-gray-400">2 hours ago</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-semibold text-xs">SM</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Sarah Miller</p>
                                    <p class="text-xs text-gray-500">Completed training session</p>
                                    <p class="text-xs text-gray-400">4 hours ago</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 font-semibold text-xs">RJ</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Robert Johnson</p>
                                    <p class="text-xs text-gray-500">Updated project timeline</p>
                                    <p class="text-xs text-gray-400">6 hours ago</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-semibold text-xs">EM</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Emily Chen</p>
                                    <p class="text-xs text-gray-500">Joined team meeting</p>
                                    <p class="text-xs text-gray-400">1 day ago</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>


