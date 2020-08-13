<template>
	<div>
		<div v-if="loading" class="container pt-md-6rem">
			<v-row>
				<h1>
					<v-progress-circular indeterminate class="mr-5"></v-progress-circular>Loading...
				</h1>
			</v-row>
			<v-progress-linear indeterminate></v-progress-linear>
		</div>
		<div v-if="!loading" class="mx-5 pt-md-6rem">
			<v-row>
				<h1>
					LATEST DATA
					<v-btn @click="getLatest()" class="ml-3">
						<v-icon class="mr-3 grey--text lighten-1">fa fa-sync</v-icon>Refresh Data
					</v-btn>
					<v-btn @click="saveLatest()" class="ml-3">
						<v-icon class="mr-3 grey--text lighten-1">fa fa-save</v-icon>Update DB
					</v-btn>
				</h1>
			</v-row>
			<v-simple-table>
				<thead>
					<th>alliance_id</th>
					<th>solar_system_id</th>
					<th>structure_id</th>
					<th>structure_type_id</th>
					<th>vulnerability_occupancy_level</th>
					<th>vulnerable_start_time</th>
					<th>vulnerable_end_time</th>
					<th>Live Timer</th>
				</thead>
				<tbody>
					<tr v-for="(item, index) in info" :key="index">
						<td>{{ item.alliance_id}}</td>
						<td>{{ item.solar_system_id}}</td>
						<td>{{ item.structure_id}}</td>
						<td>{{ item.structure_type_id}}</td>
						<td>{{ item.vulnerability_occupancy_level}}</td>
						<!-- <td>{{ item.vulnerable_start_time}}</td> -->
						<!-- <td>{{ item.vulnerable_end_time}}</td> -->
						<td>{{ fromNowStart(item) }}</td>
						<td>{{ fromNowEnd(item) }}</td>
						<td>
							<countdown
								v-if="startCounterDown(item) > 0"
								:time="startCounterDown(item)"
								auto-start
								class="red--text"
							>
								<template
									slot-scope="props"
								>Vulnerable In: {{ props.days }} days, {{ props.hours }} hours, {{ props.minutes }} minutes, {{ props.seconds }} seconds.</template>
							</countdown>
							<!-- </td>? -->
							<!-- <td> -->
							<countdown
								v-if="endCounterDown(item) > 0"
								:time="endCounterDown(item)"
								auto-start
								class="teal--text"
							>
								<template
									slot-scope="props"
								>Vulnerable Remaining: {{ props.days }} days, {{ props.hours }} hours, {{ props.minutes }} minutes, {{ props.seconds }} seconds.</template>
							</countdown>
						</td>
					</tr>
				</tbody>
			</v-simple-table>
		</div>
		<v-dialog v-model="saving" persistent max-width="290">
			<v-card class="top-border--teal text-center">
				<v-card-title class="headline">Please wait while the database is updated</v-card-title>
				<v-progress-circular indeterminate color="teal" size="70" width="7" class="my-3"></v-progress-circular>
				<v-card-text>We are saving the latest data from ESI to our database. Please be patient.</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
import Axios from "axios";
import moment from "moment";
export default {
	data() {
		return {
			loading: true,
			saving: false,
			info: [],
			response: null
		};
	},
	mounted() {
		console.log("MOUNTED");
		this.getLatest();
	},
	methods: {
		getLatest() {
			this.loading = true;
			axios
				.get(
					"https://esi.evetech.net/dev/sovereignty/structures/?datasource=tranquility"
				)
				.then(response => {
					this.response = response;
					this.info = response.data;
					this.loading = false;
				});
		},
		saveLatest() {
			this.saving = true;
			this.loading = true;
			axios.post("/saveLatest", this.response).then(res => {
				this.saving = false;
				this.loading = false;
			});
		},
		startCounterDown(item) {
			var today = new Date();
			var date =
				today.getFullYear() +
				"-" +
				(today.getMonth() + 1) +
				"-" +
				today.getDate();
			var time =
				today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
			var dateTime = date + " " + time;
			var a = moment(item.vulnerable_start_time);
			var b = moment(dateTime);
			return a.diff(b);
		},
		endCounterDown(item) {
			var today = new Date();
			var date =
				today.getFullYear() +
				"-" +
				(today.getMonth() + 1) +
				"-" +
				today.getDate();
			var time =
				today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
			var dateTime = date + " " + time;
			var a = moment(item.vulnerable_start_time);
			var b = moment(dateTime);
			return b.diff(a);
		},
		fromNowStart(item) {
			return moment(item.vulnerable_start_time).fromNow();
		},
		fromNowEnd(item) {
			return moment(item.vulnerable_end_time).fromNow();
		}
	},
	computed: {}
};
</script>
