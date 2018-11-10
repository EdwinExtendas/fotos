<template>
    <div>
        <button type="button" :class="btn_class" v-on:click="showAlbum">
            Album
        </button>
        <modal name="album-select" @before-open="beforeOpen"
            :width="400"
            :height="600">
            <div class="card">
                <div class="card-header">Category selecteren <button @click='toggle()' class="btn-success float-right" style="height: 25px; line-height: 1px;">+</button></div>
                <v-select id="template" v-model="album" label="name" :options="albums"></v-select>
                <div style="overflow-y: scroll; height: 516px;">
                    <div v-bind:class="{ 'display_not': new_category_input }">
                        <div class="card-body">
                            <label for="category">New category </label>
                            <button @click="addCategory()" class="btn-success float-right">+</button>
                            <input required id="category" type="text" v-model="new_category" class="form-control">
                        </div>
                    </div>

                    <div v-bind:class="{ 'loading': loading }">
                        <div v-bind:class="{ 'loader': loading }"></div>
                    </div>
                    <div v-for="album in albums">
                        <div class="album-btn" v-on:click="selectAlbum(album.id)">
                            {{album.name}}
                        </div>
                    </div>
                </div>

            </div>
        </modal>
    </div>
</template>

<script>
    import vSelect from 'vue-select'

    export default {
        name: "Album",
        props: {
            btn_class: {
                type: String,
                required: false
            },
            images: {
                type: Array,
                required: true
            }
        },
        data () {
            return {
                loading: false,
                albums: [],
                album: null,
                new_category: '',
                new_category_input: true
            }
        },
        watch: {
            album: function (value) {
                if (value) {
                    this.selectAlbum(value.id);
                }
            }
        },
        methods: {
            beforeOpen: function() {
                this.fetchAlbums();
            },
            selectAlbum: function (id) {
                if (this.loading) {
                    return;
                }

                this.loading = true;
                this.$http.post(
                    '/api/albums/'+id+'/link',
                    JSON.stringify([{images: this.images}])
                ).then(function (response) {
                        this.images = [];
                        this.loading = false;
                        this.$modal.hide('album-select');
                    }, (response) => {
                        this.loading = false;
                        console.log(response);
                    }
                );
            },
            showAlbum: function () {
                this.$modal.show('album-select')
            },
            addCategory: function () {
                this.loading = true;
                this.$http.post(
                    '/api/albums/create',
                    JSON.stringify({category: this.new_category})
                ).then(function (response) {
                        if (response.body.success) {
                            this.new_category = '';
                            this.new_category_input = true;
                            this.fetchAlbums();
                        }
                        this.loading = false;
                    }, (response) => {
                        this.loading = false;
                        console.log(response);
                    }
                );
            },
            fetchAlbums: function () {
                this.loading = true;
                this.$http.get(
                    '/api/albums'
                ).then(function (response) {
                        this.albums = JSON.parse(response.body);
                        this.loading = false;
                    }, (response) => {
                        this.loading = false;
                        console.log(response);
                    }
                );
            },
            toggle: function(){
                this.new_category_input = !this.new_category_input;
            }
        },
        components: {
            vSelect
        }
    }
</script>

<style scoped>
    .card {
        border: 0px solid #FFFFFF;
    }

    .card-body {
        border-bottom: 1px solid #c2cfd6;
    }

    .display_not {
        display: none;
    }

    .album-btn {
        border-bottom: 1px solid #e8e8e8;
        padding: 16px 24px;
        text-align: center;
        -webkit-user-select: none;
        -webkit-transition: background-color .1s ease;
        transition: background-color .1s ease;
        cursor: pointer;
        display: block;
        font-size: 18px;
        font-weight: 500;
        outline: none;
        overflow: hidden;
        position: relative;
    }
    .album-btn:hover {
        background-color: #EBEBEB;
    }
    /* Absolute Center Spinner */
    .loading {
        position: fixed;
        z-index: 999;
        height: 2em;
        width: 2em;
        overflow: show;
        margin: auto;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }

    .loader {
        border: 8px solid #cacaca; /* Light grey */
        border-top: 8px solid #3498db; /* Blue */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>