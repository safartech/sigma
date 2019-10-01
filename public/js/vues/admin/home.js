 window.onload=function(){

    var app = new Vue({
        delimiters: ['${', '}'],
        el: '#homes',
        data: {
            parents:0,
            profs:0,
            eleves:0

        },

        mounted() {
            axios.get('/ajax/load_admin_home')
                .then((response)=> {
                console.log(response.data)
            this.parents=response.data.parents
            this.profs=response.data.profs
            this.eleves=response.data.eleves

            $('#parents').html(this.parents)
            $('#profs').html(this.profs)
            $('#eleves').html(this.eleves)

        })
        .catch( (error) => {
                console.log(error.response.data);
        })

        },

        methods: {


        }
    })
} 
