<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Edit, UserPlus, Shield } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { toast } from 'vue-sonner';

// Define props from the controller
const props = defineProps({
  group: {
    type: Object as () => App.Models.Group,
    required: true
  },
  groupStats: {
    type: Object as () => {
      total_members: number;
      active_members: number;
      hods: number;
      booking_agents: number;
      invitees: number;
      access_controls: number;
    },
    required: true
  },
  availableUsers: {
    type: Array as () => App.Models.User[],
    required: true
  },
  // Group roles removed - users only have system-wide roles
});

// Form for adding new members
const addMemberForm = useForm({
  user_ids: [],
});

// Form for removing members
const removeMemberForm = useForm({
    user_ids: [],
});

// No longer needed - group members don't have roles

// Add member submission
const submitAddMembers = () => {
  addMemberForm.post(route('admin.groups.add-members', props.group.id), {
    onSuccess: () => {
      toast.success('Members added successfully.');
      addMemberForm.reset();
    },
    onError: (errors) => {
      toast.error('Failed to add members.', {
        description: Object.values(errors).join('\n'),
      });
    }
  });
};

// Remove selected member
const removeMember = (userId: number) => {
    removeMemberForm.user_ids = [userId];
    removeMemberForm.post(route('admin.groups.remove-members', props.group.id), {
        onSuccess: () => {
            toast.success('Member removed successfully.');
        },
        onError: (errors) => {
            toast.error('Failed to remove member.', {
                description: Object.values(errors).join('\n'),
            });
        }
    });
};

// Role updates no longer needed - users only have system-wide roles

</script>

<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <Heading :title="`Group: ${group.name}`" :description="group.description || 'Group details and member management.'">
        <Button as="a" :href="route('admin.groups.edit', group.id)" variant="outline">
          <Edit class="h-4 w-4 mr-2" />
          Edit Group
        </Button>
      </Heading>

      <!-- Group Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Total Members</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ groupStats.total_members }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Active Members</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ groupStats.active_members }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>System Roles</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">Members by system role</p>
            <div class="mt-2 space-y-1">
              <div class="flex justify-between text-sm">
                <span>HODs:</span>
                <span class="font-medium">{{ groupStats.hods }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Agents:</span>
                <span class="font-medium">{{ groupStats.booking_agents }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Invitees:</span>
                <span class="font-medium">{{ groupStats.invitees }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Access Rules</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ groupStats.access_controls }}</p>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Members Management -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle>Group Members</CardTitle>
            <CardDescription>Manage users in this group.</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">System Role</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="member in group.members" :key="member.id">
                    <td class="px-4 py-2 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</div>
                      <div class="text-sm text-gray-500">{{ member.email }}</div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                        'bg-purple-100 text-purple-800': member.user_type === 'hod',
                        'bg-blue-100 text-blue-800': member.user_type === 'booking_agent',
                        'bg-green-100 text-green-800': member.user_type === 'invitee',
                        'bg-gray-100 text-gray-800': !['hod', 'booking_agent', 'invitee'].includes(member.user_type)
                      }">
                        {{ member.user_type_display_name || member.user_type.replace('_', ' ').toUpperCase() }}
                      </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right">
                      <Button @click="removeMember(member.id)" variant="ghost" size="sm">
                        <Trash2 class="h-4 w-4 text-red-500" />
                      </Button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <!-- Add Members -->
        <Card>
          <CardHeader>
            <CardTitle>Add New Members</CardTitle>
            <CardDescription>Select users to add to the group.</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submitAddMembers" class="space-y-4">
              <div>
                <label for="users" class="block text-sm font-medium text-gray-700">Select Users to Add</label>
                <p class="text-sm text-gray-500 mb-2">Users will be added as members. Their system roles determine their permissions.</p>
                <select multiple v-model="addMemberForm.user_ids" id="users" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm h-48">
                  <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                    {{ user.first_name }} {{ user.last_name }} ({{ user.user_type_display_name || user.user_type.replace('_', ' ').toUpperCase() }}) - {{ user.email }}
                  </option>
                </select>
              </div>
              <Button type="submit" :disabled="addMemberForm.processing">
                <UserPlus class="h-4 w-4 mr-2" />
                Add Members
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>

      <!-- Access Controls -->
      <Card>
        <CardHeader>
          <CardTitle>Access Control Rules</CardTitle>
          <CardDescription>Resources this group has been granted access to.</CardDescription>
        </CardHeader>
        <CardContent>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Resource Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Resource Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Access Level</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="group.access_controls.length === 0">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">No access control rules defined for this group.</td>
                        </tr>
                        <tr v-for="ac in group.access_controls" :key="ac.id">
                            <td class="px-4 py-2 whitespace-nowrap">{{ ac.resource_type }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ ac.resource ? ac.resource.name : 'N/A' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ ac.access_level }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
      </Card>

    </div>
  </AppLayout>
</template>
