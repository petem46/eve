<template>
	<div>
		<v-container dark>
			<h1>
				<v-icon class="mr-5 blue--text">fa fa-rocket</v-icon>ESI TECH - SYSTEMS
			</h1>
			<p>
				<span
					class="ml-3 text-overline orange--text"
					v-if="fetching_ids"
				>Contacting ESI Tech API :: {{this.all_system_ids.length}}</span>
				<span
					class="ml-3 text-overline blue--text"
					v-if="fetching_names && !fetching_ids"
				>ESI Tech API Contection Success... Acquiring System Data :: {{this.system_data.length}}/{{this.all_system_ids.length}}</span>
				<span
					class="ml-3 text-overline green--text"
					v-if="!loading"
				>ESI Tech API Data Compilation Complete :: {{this.system_data.length}}/{{this.all_system_ids.length}}</span>
			</p>
			<v-card>
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
					:headers="headers"
					:items="systems"
					item-key="id"
					:loading="loading"
					loading-text="Loading..."
					:search="search"
					sort-by="name"
					must-sort
					class="text-overline"
				>
					<template v-slot:item.name="{ item }">
						<v-icon class="mr-3 blue--text">fab fa-connectdevelop</v-icon>
						{{item.name}}
					</template>
					<template v-slot:item.id="{ item }">
						<!-- <v-chip> -->
						<v-icon class="mr-3 orange--text">fas fa-fingerprint</v-icon>
						<span class="text-overline">{{item.id}}</span>
						<!-- </v-chip> -->
					</template>
					<template v-slot:item.actions="{ item }">
						<v-btn>Action</v-btn>
						<v-btn>Action</v-btn>
					</template>
				</v-data-table>
				<v-card-actions>
					<v-btn outlined @click="saveSystemData">Save Data</v-btn>
				</v-card-actions>
			</v-card>
		</v-container>
	</div>
</template>
<script>
import Axios from "axios";
import moment from "moment";
export default {
	data() {
		return {
			loading: true,
			fetching_ids: true,
			fetching_names: true,
			systems: [],
			all_system_ids: [],
			system_data: [],
			headers: [
				{ text: "Designation", value: "name", width: "60%" },
				{ text: "Identification Code", value: "id", width: "20%" },
				{ text: "", value: "actions", width: "20%" }
			],
			search: ""
		};
	},
	async mounted() {
		this.loading = true;
		await this.getSystems();
		await this.getSystemsIDs(); // get all system ids from ESI Tech API
		await this.getSystemNames(); // get all system names from ESI Tech API
		this.loading = false;
	},
	methods: {
		async getSystems() {
			this.loading = true;
			await axios.get("/getSystems").then(res => {
				if (res.status == 200) {
					this.systems = res.data.systems;
				}
				this.loading = false;
			});
			// this.loading = false;
		},
		async getSystemsIDs() {
			this.fetching_ids = true;
			this.all_system_ids = [];
			const res = await axios
				.get(
					"https://esi.evetech.net/latest/universe/systems/?datasource=tranquility"
				)
				.then(res => {
					if (res.status == 200) {
						for (var i = 0; i < res.data.length; i++) {
							this.all_system_ids.push(res.data[i]);
						}
					}
				});
			this.fetching_ids = false;
		},
		async getSystemNames() {
			this.fetching_names = true;
			this.system_data = [];
			let l = this.all_system_ids.length;
			var start = 0;
			var end = 0;
			var loop = Math.round(l / 1000);
			var lastloop = loop * 1000;
			var lastloopend = loop * 1000 + (l % 1000);
			var lastloopcount = l % 1000;
			// GET FIRST 1000
			for (var x = 0; x < loop; x++) {
				this.system_fetch = [];
				if (x == 0) {
					let end = 999;
					for (var s = start; s <= end; s++) {
						this.system_fetch.push(this.all_system_ids[s]);
					}
					this.system_message = "Getting System Names " + start + "/" + l;
					const system_data_response = await axios.post(
						"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
						this.system_fetch
					);
					if (system_data_response.error) {
						this.system_message = "ERROR";
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
				// LOOP GET NEXT x 1000 FOR loop TIMES
				if (x > 0) {
					let start = 1000 * x;
					let end = 1000 * x + 999;
					for (var s = start; s <= end; s++) {
						this.system_fetch.push(this.all_system_ids[s]);
					}
					const system_data_response = await axios.post(
						"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
						this.system_fetch
					);
					for (var s = 0; s <= 1000; s++) {
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
				this.system_fetch.push(this.all_system_ids[x]);
			}
			const system_data_response = await axios.post(
				"https://esi.evetech.net/latest/universe/names/?datasource=tranquility",
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
			this.fetching_names = false;
		},
		async saveSystemData() {
			const emptySystemsTable = await axios.post("/emptySystemsTable");
			this.saving_system_data = true;
			const system_names_response = await axios.post(
				"/saveSystemData",
				this.system_data
			);
			this.saving_system_data = false;
		}
	}
};
</script>
