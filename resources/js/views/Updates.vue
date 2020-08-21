<template>
	<div>
		<div class="ma-5">
			<p>
				<span v-if="loading && !fetching_names" class="ml-3 text-overline orange--text">
					Contacting ESI EveTech API :: Downloading data...
					<v-icon class="mx-3 orange--text">fas fa-satellite-dish fa-sm fa-fw</v-icon>
				</span>
				<span class="ml-3 text-overline green--text" v-if="fetching_names || fetching_systems">
					ESI EveTech API Connection :: Successful
					<v-icon class="mx-3 green--text">fas fa-check fa-sm fa-fw</v-icon>
				</span>
			</p>
			<p>
				<span class="ml-3 text-overline blue--text" v-if="fetching_names && !fetching_systems">
					ESI EveTech API Contection Success... Acquiring Alliance Data :: {{this.alliance_data.length}}/{{this.all_alliance_ids.length}}
					<v-icon class="mx-3 blue--text">fa fa-cloud-download-alt fa-sm fa-fw</v-icon>
				</span>
				<span class="ml-3 text-overline green--text" v-if="fetching_systems">
					ESI EveTech API Data Compilation Complete :: {{this.alliance_data.length}}/{{this.all_alliance_ids.length}}
					<v-icon class="mx-3 green--text">fas fa-check fa-sm fa-fw</v-icon>
				</span>
			</p>
			<p>
				<span class="ml-3 text-overline orange--text" v-if="fetching_systems">
					Contacting ESI EveTech API :: Acquiring System Data... {{this.system_data.length}}
					<v-icon class="mx-3 orange--text">fas fa-network-wired fa-sm fa-fw</v-icon>
				</span>
				<!-- <span
					class="ml-3 text-overline green--text"
					v-if="!loading && !fetching_names && !fetching_systems"
				>
					ESI EveTech API Data Compilation Complete :: {{this.system_data.length}}/{{this.system_data.length}}
					<v-icon class="mx-3 green--text">fas fa-check fa-sm fa-fw</v-icon>
				</span>-->
			</p>
			<v-card v-if="loading">
				<v-progress-linear indeterminate color="blue"></v-progress-linear>
			</v-card>
			<v-card v-if="!loading && !fetching_names && !fetching_systems">
				<v-card-title>Sovereignty Structure Vulnerability Data</v-card-title>
				<v-card-text>
					<v-text-field
						v-model="search"
						append-icon="fa fa-search"
						label="Search"
						single-line
						hide-details
					></v-text-field>
				</v-card-text>
				<v-data-table
					:loading="fetching_names && loading && fetching_systems"
					loading-text="Downloading data from ESI EveTech..."
					:headers="headers"
					:items="info"
					item-key="key"
					class="elevation-1"
					:search="search"
					sort-by="name"
					must-sort
				>
					<template v-slot:item.name="{ item }">
						<v-icon v-if="endCounterDown(item) > 0" color="red" class="mr-3">mdi-death-star-variant</v-icon>
						<v-icon v-if="startCounterDown(item) > 0" color="green" class="mr-3">mdi-shield-star-outline</v-icon>
            {{
						item.name
						}}
					</template>
					<template v-slot:item.system_name="{ item }">
						<v-icon color="yellow" class="mr-3">mdi-star-four-points-outline</v-icon>
						<span class="yellow--text">
							{{
							item.system_name
							}}
						</span>
					</template>
					<template v-slot:item.vulnerable_start_time="{ item }">
						<countdown
							v-if="startCounterDown(item) > 0"
							:time="startCounterDown(item)"
							auto-start
							class="yellow--text"
						>
							<template slot-scope="props">
								Warning: {{ props.days }} days, {{ props.hours }} hours,
								{{ props.minutes }} minutes,
								{{ props.seconds }} seconds until in danger
							</template>
						</countdown>
						<countdown
							v-if="endCounterDown(item) > 0"
							:time="endCounterDown(item)"
							auto-start
							class="red--text"
						>
							<template slot-scope="props">
								Vulnerable - {{ props.days }} days,
								{{ props.hours }} hours, {{ props.minutes }} minutes,
								{{ props.seconds }} seconds remaining
							</template>
						</countdown>
					</template>
				</v-data-table>
			</v-card>
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
			search: "",
			fetching_ids: false,
			fetching_names: false,
			fetching_systems: false,
			saving: false,
			info: [],
			response: null,
			all_alliance_ids: [],
			all_system_ids: [],
			system_fetch_ids: [],
			system_fetch: [],
			alliance_fetch: [],
			alliance_data: [],
			systems: [],
			system_data: [],
			system_data_length: 0,
			alliance_response: null,
			systems_response: null,
			headers: [
				{ text: "Designation", value: "name", width: "35%" },
				{ text: "System", value: "system_name" },
				// { text: "structure__id", value: "structure_id" },
				{ text: "Structure type", value: "structure_type_name" },
				{
					text: "Occupancy Level",
					value: "vulnerability_occupancy_level"
				},
				{ text: "Vulnerable Start Time", value: "vulnerable_start_time" }
				// { text: "Vulernable End Time", value: "vulnerable_end_time" }
			]
		};
	},
	async mounted() {
		this.loading = true;
		await this.getLatest();
		// await this.getSystems();
		await this.getAlliancesIDs();
		await this.getAllianceNames();
		await this.getSystemIDs();
		await this.getSystemNames();
		await this.setStructureTypes();
		await this.matchLatesttoNames();
		this.loading = false;
	},
	methods: {
		async getLatest() {
			this.loading = true;
			this.message = "Getting Latest Data";
			this.response = await axios.get(
				"https://esi.evetech.net/dev/sovereignty/structures/?datasource=tranquility"
			);
			this.info = this.response.data;
			this.info_length = this.info.length;
		},
		async getSystems() {
			this.loading = true;
			await axios.get("/getSystems").then(res => {
				if (res.status == 200) {
					this.systems = res.data.systems;
				}
				// this.loading = false;
			});
		},
		async getAlliancesIDs() {
			this.fetching_ids = true;
			this.all_alliance_ids = [];
			const res = await axios
				.get("https://esi.evetech.net/dev/alliances/?datasource=tranquility")
				.then(res => {
					if (res.status == 200) {
						for (var i = 0; i < res.data.length; i++) {
							this.all_alliance_ids.push(res.data[i]);
						}
					}
					console.log(res.status);
				});
			this.fetching_ids = false;
		},
		async getAllianceNames() {
			this.fetching_names = true;
			this.alliance_data = [];
			let l = this.all_alliance_ids.length;
			var start = 0;
			var end = 0;
			var loop = Math.round(l / 500);
			var lastloop = loop * 500;
			var lastloopend = loop * 500 + (l % 500);
			var lastloopcount = l % 500;
			// GET FIRST 500
			for (var x = 0; x < loop; x++) {
				this.alliance_fetch = [];
				if (x == 0) {
					let end = 499;
					for (var s = start; s <= end; s++) {
						this.alliance_fetch.push(this.all_alliance_ids[s]);
					}
					this.alliance_message = "Getting Alliance Names " + start + "/" + l;
					const alliance_data_response = await axios
						.post(
							"https://esi.evetech.net/dev/universe/names/?datasource=tranquility",
							this.alliance_fetch
						)
						.then(alliance_data_response => {
							console.log(alliance_data_response.status);
							if (alliance_data_response.status == 200) {
								for (var s = start; s <= end; s++) {
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
						});
				}
				// LOOP GET NEXT x 500 FOR loop TIMES
				if (x > 0) {
					let start = 500 * x;
					let end = 500 * x + 499;
					for (var s = start; s <= end; s++) {
						this.alliance_fetch.push(this.all_alliance_ids[s]);
					}
					const alliance_data_response = await axios.post(
						"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
						this.alliance_fetch
					);
					for (var s = 0; s <= 500; s++) {
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
				if (
					typeof alliance_data_response.data[s] == "undefined" ||
					alliance_data_response.data[s] === null
				) {
					console.log("F");
					continue;
				}

				this.alliance_data.push(alliance_data_response.data[s]);
			}
			this.alliance_fetch = [];
		},
		matchLatesttoNames() {
			// console.log(item)
			this.fetching_names = true;
			let l = this.info.length;
			for (var i = 0; i < l; i++) {
				if (typeof this.info[i] == "undefined" || this.info[i] === null) {
					continue;
				}
				this.info[i].name = this.alliance_data.find(
					({ id }) => id === this.info[i].alliance_id
				).name;
				this.info[i].system_name = this.system_data.find(
					({ id }) => id === this.info[i].solar_system_id
				).name;
			}
			this.fetching_names = false;
		},
		async getSystemIDs() {
			this.fetching_systems = true;
			this.all_system_ids = [];
			let l = this.info.length;
			for (var i = 0; i < l; i++) {
				if (
					typeof this.info[i] == "undefined" ||
					this.info[i] === null ||
					this.info[i].solar_system_id == "None"
				) {
					console.log("F");
					continue;
				}
				if (Number.isInteger(this.info[i].solar_system_id)) {
					this.all_system_ids.push(this.info[i].solar_system_id);
				}
			}
			this.system_fetch_ids = Array.from(new Set(this.all_system_ids));
		},
		async xgetSystemNames() {
			let l = this.system_fetch_ids.length;
			for (var i = 0; i < l; i++) {
				if (!Number.isInteger(this.system_fetch_ids[i])) {
					console.log("F-".this.system_fetch_ids[i]);
				}
			}
		},
		async getSystemNames() {
			this.fetching_systems = true;
			this.system_data = [];
			let l = this.system_fetch_ids.length;
			var start = 0;
			var end = 0;
			var loop = Math.round(l / 500);
			var lastloop = loop * 500;
			var lastloopend = loop * 500 + (l % 500);
			var lastloopcount = l % 500;
			// GET FIRST 500
			for (var x = 0; x < loop; x++) {
				this.system_fetch = [];
				if (x == 0) {
					let end = 499;
					for (var s = start; s <= end; s++) {
						this.system_fetch.push(this.system_fetch_ids[s]);
					}
					this.alliance_message = "Getting Alliance Names " + start + "/" + l;
					const system_data_response = await axios.post(
						"https://esi.evetech.net/v2/universe/names/?datasource=tranquility",
						this.system_fetch
					);
					if (system_data_response.error) {
						this.alliance_message = "ERROR";
					}
					for (var s = start; s <= end; s++) {
						if (
							typeof system_data_response.data[s] == "undefined" ||
							system_data_response.data[s] === null
						) {
							console.log("F");
							continue;
						}
						this.system_data.push(system_data_response.data[s]);
					}
				}
				// LOOP GET NEXT x 500 FOR loop TIMES
				if (x > 0) {
					let start = 500 * x;
					let end = 500 * x + 499;
					for (var s = start; s <= end; s++) {
						this.system_fetch.push(this.system_fetch_ids[s]);
					}
					const system_data_response = await axios.post(
						"https://esi.evetech.net/v2/universe/names/?datasource=tranquility",
						this.system_fetch
					);
					for (var s = 0; s <= 500; s++) {
						// console.log(system_data_response.data[s]);
						if (
							typeof system_data_response.data[s] == "undefined" ||
							system_data_response.data[s] === null
						) {
							console.log("F");
							continue;
						}

						this.system_data.push(system_data_response.data[s]);
					}
				}
			}
			// LAST LOOP - GET REMAINDER lastloopcount IDS
			this.system_fetch = [];
			for (var x = lastloop; x < lastloopend; x++) {
				this.system_fetch.push(this.system_fetch_ids[x]);
			}
			const system_data_response = await axios.post(
				"https://esi.evetech.net/v2/universe/names/?datasource=tranquility",
				this.system_fetch
			);
			for (var s = 0; s <= lastloopcount; s++) {
				if (
					typeof system_data_response.data[s] == "undefined" ||
					system_data_response.data[s] === null
				) {
					console.log("F");
					continue;
				}

				this.system_data.push(system_data_response.data[s]);
			}
			this.system_fetch = [];
			this.fetching_systems = false;
		},
		async setStructureTypes() {
			let l = this.info.length;
			for (var i = 0; i < l; i++) {
				if (this.info[i].structure_type_id == "32226") {
					this.info[i].structure_type_name = "TCU";
				}
				if (this.info[i].structure_type_id == "32458") {
					this.info[i].structure_type_name = "IHUB";
				}
			}
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
