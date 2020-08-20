<template>
	<div>
		<!-- <div v-if="!getNamesDone" class="container pt-md-6rem">
			<v-row>
				<h1>
					<v-progress-circular indeterminate class="mr-5"></v-progress-circular>
					{{this.message}}
				</h1>
			</v-row>
			<v-progress-linear indeterminate></v-progress-linear>
		</div>-->
		<div class="mx-5 pt-md-6rem">
			<v-row>
				<h1>
					LATEST DATA
					<v-btn @click="getAlliancesIDs()" class="ml-3">
						<v-icon class="mr-3 grey--text lighten-1">fa fa-sync</v-icon>getAlliancesIDs
					</v-btn>
					<v-btn @click="getAllianceNames()" class="ml-3">
						<v-icon class="mr-3 grey--text lighten-1">fa fa-sync</v-icon>
						{{alliance_message}}
					</v-btn>
					<v-btn :loading="saving_alliance_data" @click="saveAllianceData()" class="ml-3">
						<v-icon class="mr-3 grey--text lighten-1">fa fa-save</v-icon>
						{{saving_alliances_message}}
					</v-btn>
				</h1>
			</v-row>
			<v-row>
				<h1>Alliances in ESI Database: {{this.all_alliance_ids.length}}</h1>
			</v-row>
			<v-row>
				<h1>Alliances names pulled from ESI Database: {{this.alliance_data.length}}</h1>
			</v-row>
			<v-data-table
				v-if="getNamesDone == 'eggs'"
				:headers="headers"
				:items="info"
				:loading="!getNamesDone"
				loading-text="Loading... Please wait"
				item-key="key"
				class="elevation-1"
			>
				<template v-slot:item.alliance_name="{ item }">
					{{
					item.alliance_name
					}}
				</template>
				<template v-slot:item.system_name="{ item }">
					<v-chip>
						{{
						item.system_name
						}}
					</v-chip>
				</template>
				<template v-slot:item.vulnerable_start_time="{ item }">
					<countdown
						v-if="startCounterDown(item) > 0"
						:time="startCounterDown(item)"
						auto-start
						class="red--text"
					>
						<template slot-scope="props">
							Vulnerable In: {{ props.days }} days, {{ props.hours }} hours,
							{{ props.minutes }} minutes,
							{{ props.seconds }} seconds.
						</template>
					</countdown>
					<countdown
						v-if="endCounterDown(item) > 0"
						:time="endCounterDown(item)"
						auto-start
						class="teal--text"
					>
						<template slot-scope="props">
							Vulnerable Remaining: {{ props.days }} days,
							{{ props.hours }} hours, {{ props.minutes }} minutes,
							{{ props.seconds }} seconds.
						</template>
					</countdown>
				</template>
			</v-data-table>
		</div>
		<v-dialog v-model="saving" persistent max-width="290">
			<v-card class="top-border--teal text-center">
				<v-card-title class="headline">Please wait while the database is updated</v-card-title>
				<v-progress-circular indeterminate color="teal" size="70" width="7" class="my-3"></v-progress-circular>
				<v-card-text>
					We are saving the latest data from ESI to our database. Please be
					patient.
				</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
import Axios from "axios";
import moment from "moment";
import { stringify } from "querystring";
// import VueFilterDateFormat from "@vuejs-community/vue-filter-date-format";
// import VueFilterDateParse from "@vuejs-community/vue-filter-date-parse";
export default {
	data() {
		return {
			loading: true,
			message: "Loading...",
			alliance_message: "Waiting...",
			saving_alliances_message: "Ready...",
			saving_alliance_data: false,
			getNamesDone: false,
			saving: false,
			info: [],
			info_length: 0,
			response: null,
			alliance_data: [],
			alliance_data_length: 0,
			system_data: [],
			system_data_length: 0,
			alliance_response: null,
			systems_response: null,
			headers: [
				{ text: "alliance_name", value: "alliance_name" },
				{ text: "system_name", value: "system_name" },
				{ text: "structure__id", value: "structure_id" },
				{ text: "structure_type_name", value: "structure_type_name" },
				{
					text: "vulnerability_occupancy_level",
					value: "vulnerability_occupancy_level"
				},
				{ text: "vulnerable_start_time", value: "vulnerable_start_time" },
				{ text: "vulnerable_end_time", value: "vulnerable_end_time" }
			],
			alliance_fetch: [],
			all_alliance_ids: []
		};
	},
	async mounted() {
		console.log("MOUNTED");
		await this.getLatest();
		// await this.setVulnerable();
		// await this.getSolarSystemData();
		await this.getAlliancesIDs();
		await this.getAllianceNames();
	},
	methods: {
		async getLatest() {
			this.message = "Getting Latest Data";
			this.loading = true;
			this.response = await axios.get(
				"https://esi.evetech.net/dev/sovereignty/structures/?datasource=tranquility"
			);
			this.info = this.response.data;
			this.info_length = this.info.length;
			this.loading = false;
		},
		async getAlliancesIDs() {
			this.all_alliance_ids = [];
			const res = await axios
				.get("https://esi.evetech.net/latest/alliances/?datasource=tranquility")
				.then(res => {
					if (res.status == 200) {
						for (var i = 0; i < res.data.length; i++) {
							this.all_alliance_ids.push(res.data[i]);
						}
					}
				});
		},
		async getAllianceNames() {
			this.alliance_data = [];
			let l = this.all_alliance_ids.length;
			var start = 0;
			var end = 0;
			var loop = Math.round(l / 1000);
			var lastloop = loop * 1000;
			var lastloopend = loop * 1000 + (l % 1000);
			var lastloopcount = l % 1000;
			// GET FIRST 1000
			for (var x = 0; x < loop; x++) {
				this.alliance_fetch = [];
				if (x == 0) {
					let end = 999;
					for (var s = start; s <= end; s++) {
						this.alliance_fetch.push(this.all_alliance_ids[s]);
					}
					this.alliance_message = "Getting Alliance Names " + start + "/" + l;
					const alliance_data_response = await axios.post(
						"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
						this.alliance_fetch
					);
					if (alliance_data_response.error) {
						this.alliance_message = "ERROR";
					}
					for (var s = start; s <= end; s++) {
						// console.log(alliance_data_response.data[s]);
						if (
							typeof alliance_data_response.data[s] == "undefined" ||
							alliance_data_response.data[s] === null
						) {
							console.log("F");
							continue;
						}

						this.alliance_data.push(alliance_data_response.data[s]);
					}
				}
				// LOOP GET NEXT x 1000 FOR loop TIMES
				if (x > 0) {
					let start = 1000 * x;
					let end = 1000 * x + 999;
					for (var s = start; s <= end; s++) {
						this.alliance_fetch.push(this.all_alliance_ids[s]);
					}
					const alliance_data_response = await axios.post(
						"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
						this.alliance_fetch
					);
					for (var s = 0; s <= 1000; s++) {
						// console.log(alliance_data_response.data[s]);
						if (
							typeof alliance_data_response.data[s] == "undefined" ||
							alliance_data_response.data[s] === null
						) {
							console.log("F");
							continue;
						}

						this.alliance_data.push(alliance_data_response.data[s]);
					}
					this.alliance_message = "Getting Alliance Names " + end + "/" + l;
				}
			}
			// LAST LOOP - GET REMAINDER lastloopcount IDS
			this.alliance_fetch = [];
			for (var x = lastloop; x < lastloopend; x++) {
				this.alliance_fetch.push(this.all_alliance_ids[x]);
			}
			const alliance_data_response = await axios.post(
				"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
				this.alliance_fetch
			);
			for (var s = 0; s <= lastloopcount; s++) {
				// console.log(alliance_data_response.data[s]);
				if (
					typeof alliance_data_response.data[s] == "undefined" ||
					alliance_data_response.data[s] === null
				) {
					console.log("F");
					continue;
				}

				this.alliance_data.push(alliance_data_response.data[s]);
			}
			this.alliance_message = "Getting Alliance Names " + lastloopend + "/" + l;
		},
		async saveAllianceData() {
			const emptyAlliancesTable = await axios.post("/emptyAlliancesTable");
			this.saving_alliance_data = true;
			const alliance_names_response = await axios.post(
				"/saveAllianceData",
				this.alliance_data
			);
			this.saving_alliance_data = false;
		},
		async getSolarSystemData() {
			this.message = "Getting Solar System Names 0/" + this.info_length;
			this.getNamesDone = false;
			let l = this.info.length;
			for (var i = 0; i < l; i++) {
				const systems_response = await axios.get(
					"https://esi.evetech.net/latest/universe/systems/" +
						this.info[i].solar_system_id +
						"/?datasource=tranquility"
				);
				this.systems_response = systems_response.data;
				this.info[i].system_name = this.systems_response.name;
				this.system_data.push(this.systems_response);
				this.system_data_length = this.system_data.length;
				this.message =
					"Getting Solar System Names " +
					this.system_data_length +
					"/" +
					this.info_length;
			}
			this.getNamesDone = true;
			this.message = "Done";
		},
		async setStructureTypes() {
			this.message = "Getting Solar System Names 0/" + this.info_length;
			this.getNamesDone = false;
			let l = this.info.length;
			for (var i = 0; i < l; i++) {
				if (this.info[i].structure_type_id == "32226") {
					this.info[i].structure_type_name = "TCU";
				}
				if (this.info[i].structure_type_id == "32458") {
					this.info[i].structure_type_name = "IHUB";
				}
			}
			this.getNamesDone = true;
			this.message = "Done";
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
			return moment(item.vulnerable_start_time);
		},
		fromNowEnd(item) {
			return moment(item.vulnerable_end_time);
		}
	},
	computed: {}
};
</script>
