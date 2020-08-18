<template>
	<div>
		<v-container dark>
			<h1>
				<v-icon class="mr-2 grey--text lighten-1">fa fa-rocket</v-icon>ALLIANCES
			</h1>
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
				<v-data-table :headers="headers" :items="alliances" item-key="id" :loading="loading" :search="search" sort-by="name" must-sort>
					<template v-slot:item.name="{ item }">
						<v-icon class="mr-3">fab fa-connectdevelop</v-icon>
						{{item.name}}
					</template>
					<template v-slot:item.actions="{ item }">
						<v-btn>Action</v-btn>
						<v-btn>Action</v-btn>
					</template>
				</v-data-table>
			</v-card>
		</v-container>
	</div>
</template>
<script>
import Axios from "axios";
import moment from "moment";
// import VueFilterDateFormat from "@vuejs-community/vue-filter-date-format";
// import VueFilterDateParse from "@vuejs-community/vue-filter-date-parse";
export default {
	data() {
		return {
			loading: true,
			message: "Loading...",
			alliance_message: "Waiting...",
			alliances: [],
			headers: [
				{ text: "Alliance Name", value: "name", width: "80%" },
				{ text: "", value: "actions" }
			],
			search: ""
		};
	},
	mounted() {
		this.getAlliances();
	},
	methods: {
		getAlliances() {
			this.loading = true;
			axios.get("/getAlliances").then(res => {
				if (res.status == 200) {
          this.alliances = res.data.alliances;
          this.loading = false;
				}
			});
		}
	}
};
</script>
