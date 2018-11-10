<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <b-row class="justify-content-center">
                <b-col md="6">
                    <b-card-group>
                        <b-card no-body class="p-4">
                            <b-card-body>
                                <b-form novalidate ref="form" @submit.prevent="signIn">
                                    <b-col cols="12" class="x10-loginlogo x10-logo-50"><img src="../../static/img/logo-dog.gif"/></b-col>
                                    <p class="text-muted">Sign In to your account</p>
                                    <b-input-group class="mb-3">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="icon-user"></i></span></div>
                                        <input required name="email" type="email" v-model="email" class="form-control" placeholder="Username">
                                    </b-input-group>
                                    <b-input-group class="mb-4">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="icon-lock"></i></span></div>
                                        <input required name="password" type="password" v-model="password" class="form-control"
                                               placeholder="Password">
                                    </b-input-group>
                                    <b-row>
                                        <b-col cols="6">
                                            <b-button type="submit" id="btn-sign-in" variant="primary" class="px-4">
                                                Login
                                            </b-button>
                                        </b-col>
                                    </b-row>
                                </b-form>
                            </b-card-body>
                        </b-card>
                    </b-card-group>
                </b-col>
            </b-row>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'Login',
        data: function () {
            return {
                email: '',
                password: ''
            };
        },
        methods: {
            signIn: function (event) {
                let form = this.$refs.form;
                if (false === form.checkValidity())
                {
                    form.classList.add('was-validated'); // show bootstrap validation styling
                    form.querySelectorAll(':invalid')[0].focus(); // focus on first invalid element
                    return;
                }

                document.getElementById("btn-sign-in").disabled = true;

                this.$http.post(
                    '/login',
                    JSON.stringify({email: this.email, password: this.password})
                ).then(function (response) {
                    this.$router.push({name: 'Dashboard'});
                }, function (error) {
                    document.getElementById("btn-sign-in").disabled = false;
                });
            }
        }
    }
</script>
