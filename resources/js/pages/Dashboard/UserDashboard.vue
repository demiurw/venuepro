<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading>My Dashboard</Heading>

            <!-- Primary Action Card -->
            <Card class="bg-gradient-to-r from-blue-500 to-blue-600 border-none">
                <CardContent class="p-8 text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Ready to book a room?</h2>
                    <p class="text-blue-100 mb-6">Find and reserve the perfect space for your next meeting</p>
                    <Button
                        class="bg-white text-blue-600 hover:bg-gray-100 font-semibold py-3 px-8 rounded-lg text-lg transition-colors duration-200 shadow-lg"
                    >
                        <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v3m0-3h3m-3 0h-3"/>
                        </svg>
                        Book a Room
                    </Button>
                </CardContent>
            </Card>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Bookings List -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v3m0-3h3m-3 0h-3"/>
                            </svg>
                            My Upcoming Bookings
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Booking 1 -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                                <div class="flex items-center space-x-4">
                                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Conference Room A</h3>
                                        <p class="text-sm text-gray-600">Tomorrow at 10:00 AM - 11:30 AM</p>
                                        <p class="text-xs text-gray-500">Team standup meeting</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                  <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                    Confirmed
                  </span>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Booking 2 -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-green-500">
                                <div class="flex items-center space-x-4">
                                    <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Executive Boardroom</h3>
                                        <p class="text-sm text-gray-600">Friday at 2:00 PM - 4:00 PM</p>
                                        <p class="text-xs text-gray-500">Client presentation</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                  <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                    Pending
                  </span>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Booking 3 -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-l-4 border-purple-500">
                                <div class="flex items-center space-x-4">
                                    <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Innovation Lab</h3>
                                        <p class="text-sm text-gray-600">Next Monday at 9:00 AM - 12:00 PM</p>
                                        <p class="text-xs text-gray-500">Workshop session</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                  <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                    Awaiting Setup
                  </span>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- View All Link -->
                            <div class="pt-4 border-t">
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                    View all bookings
                                    <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Stats & Actions -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold text-gray-900">Quick Stats</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">3</div>
                                    <div class="text-sm text-gray-600">This Week</div>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">12</div>
                                    <div class="text-sm text-gray-600">This Month</div>
                                </div>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">4</div>
                                    <div class="text-sm text-gray-600">Favorites</div>
                                </div>
                                <div class="text-center p-4 bg-orange-50 rounded-lg">
                                    <div class="text-2xl font-bold text-orange-600">89%</div>
                                    <div class="text-sm text-gray-600">Success Rate</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold text-gray-900">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <Button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v3m0-3h3m-3 0h-3"/>
                                    </svg>
                                    <span>View Calendar</span>
                                </Button>

                                <Button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>Browse Rooms</span>
                                </Button>

                                <Button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Recent Activity</span>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Room Availability -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold text-gray-900">Available Now</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-700">Meeting Room B</span>
                                    </div>
                                    <span class="text-xs text-green-600">Available</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-700">Small Conference</span>
                                    </div>
                                    <span class="text-xs text-green-600">Available</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-2 w-2 bg-red-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-700">Training Room</span>
                                    </div>
                                    <span class="text-xs text-red-600">Occupied</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/ui/Heading.vue'
import Button from '@/components/ui/Button.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

// Placeholder data
const upcomingBookings = [
    {
        id: 1,
        room: 'Conference Room A',
        date: 'Tomorrow at 10:00 AM - 11:30 AM',
        purpose: 'Team standup meeting',
        status: 'Confirmed',
        statusColor: 'bg-green-100 text-green-800',
        borderColor: 'border-blue-500',
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600'
    },
    {
        id: 2,
        room: 'Executive Boardroom',
        date: 'Friday at 2:00 PM - 4:00 PM',
        purpose: 'Client presentation',
        status: 'Pending',
        statusColor: 'bg-blue-100 text-blue-800',
        borderColor: 'border-green-500',
        iconBg: 'bg-green-100',
        iconColor: 'text-green-600'
    },
    {
        id: 3,
        room: 'Innovation Lab',
        date: 'Next Monday at 9:00 AM - 12:00 PM',
        purpose: 'Workshop session',
        status: 'Awaiting Setup',
        statusColor: 'bg-yellow-100 text-yellow-800',
        borderColor: 'border-purple-500',
        iconBg: 'bg-purple-100',
        iconColor: 'text-purple-600'
    }
]

const quickStats = [
    { value: 3, label: 'This Week', color: 'bg-blue-50 text-blue-600' },
    { value: 12, label: 'This Month', color: 'bg-green-50 text-green-600' },
    { value: 4, label: 'Favorites', color: 'bg-purple-50 text-purple-600' },
    { value: '89%', label: 'Success Rate', color: 'bg-orange-50 text-orange-600' }
]

const availableRooms = [
    { name: 'Meeting Room B', available: true },
    { name: 'Small Conference', available: true },
    { name: 'Training Room', available: false }
]
</script>
