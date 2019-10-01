/**
 * Created by User on 20/12/2018.
 */

import {url} from '../../base_url.js'
//        alert(baseUrl)
let instance = axios.create({
    baseURL : url
});


let home = {
    template:'#home',
    data(){
        return {
            cours: [],
            classes: [],
            currentCours : {
                id:0,
            },
            selectedCycle:{},
            selectedNiveau:{},
            selectedProf:{},
            selectedCycleId:null,
            selectedNiveauId:null,
            selectedProfId:null,
            jours:[],
            horaires:[],
            matieres:[],
            profs:[],
            salles:[],
            newCours:{
                classe_id:null,
                jour_id:null,
                horaire_id:null,
                matiere_id:null,
                personnel_id:null,
                salle_id:null,
            },
            // currentCours:{},
            currentHeure:{},
            currentJour:{},
            showHoraires:0,
            //  showProfs:0,
            showMatieres:1,
            showWeekends:0,
            selectedCours:{},
            selectedEvent:{
                start:{
                    format:function(){},
                },
                end:{
                    format:function(){},
                },
                seances:[],
                seance:{}
            },
            selectedSeanceId:0,
            hooveredCoursId:0,

            cdt_fc_options :   {
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    // right: 'month,basicWeek,basicDay',
                    right: 'custom1,custom2,month,agendaWeek,agendaDay,listWeek',
                    // right: 'custom2, month,basicWeek,basicDay,listWeek',
                },

                customButtons: {
                    custom1: {
                        text: 'Sem.Séances',
                        click: function() {
                            $('#cdt-calendar').fullCalendar('changeView', 'basicWeek');
                        }
                    },
                    custom2: {
                        text: 'Journ.Séances',
                        click: function() {
                            $('#cdt-calendar').fullCalendar('changeView', 'basicDay');
                        }
                    }
                },

                defaultView:'listWeek',
                height: 700,
//            contentHeight: 800,

                selectable: true,
                nowIndicator: true,
                selectHelper: true,
                unselectAuto: true,

                fixedWeekCount: false,
                showNonCurrentDates: true,
                slotDuration: '00:30:00',
                slotLabelFormat: 'h(:mm)a',
                minTime: "07:00:00",
                maxTime: "18:00:00",
                // noEventsMessage: "0 events",
                // dayPopoverFormat:"DD",
                // scrollTime: "10:00:00",
                /*slotLabelInterval:{
                 duration:"01:00"
                 },*/


                allDaySlot: true,
                allDayText: "Toute la journée",
                slotEventOverlap: true,

                titleFormat: "D MMMM YYYY",
                today:    'Aujourd\'hui',
                month:    'mois',
                week:     'semaine',
                day:      'jour',
                list:     'list',

                firstDay: 1,
                locale: 'fr',
                weekends: false,
                timeFormat: 'H:mm',
                displayEventTime: true,
                displayEventEnd: true,

                eventRender:function (eb,el) {

                },

                eventAfterRender: function(event, element) {
                    // event.color = '#0ff'
                    // console.log(element)
                    // event.matiere = "Farid"
                    // color: "#01579b",
                    // color: "#00897b",
                    var view = $('#cdt-calendar').fullCalendar('getView');
                    console.log(view.type)
                    if(view.type!="listWeek"){
                        element.css('background-color', '#01579b');
                        var cours = event.cours
                        // var moment = $('#cdt-calendar').fullCalendar('getDate');
                        // console.log(moment(event.start).format('YYYY-MM-DD'))
                        var s = event.seances.filter(s=>s.cours_id == event.id && s.date == event.start.format("YYYY-MM-DD"))[0]
                        // console.log(event.id+"/"+event.matiere+"/"+"------->"+ event.seances.length)
                        // console.log(s.length)
                        if(s){
                            if(s.cours_id==event.id && s.date == event.start.format("YYYY-MM-DD")){
                                element.css('background-color', '#b71c1c');
                            }
                        }
                    }/*else {
                     element.css('color', event.couleur);
                     }*/

                    if (event.customRender == true)
                    {

                        // var el = element.html();
                        // element.html("<div style='width:90%;float:left;'>" +  el + "</div><div style='text-align:right;' class='close'><span class='mdi mdi-print'></span></div>");
                        //...etc
                    }
                },
                select: function(startDate, endDate) {
                    // alert('selected ' + startDate.format() + ' to ' + endDate.format());
//                swal(startDate.format()+"");
                },
                eventDestroy:function(event, element){},
                dayClick: function(date, jsEvent, view) {
                    // alert('Clicked on: ' + date.format());
                    // alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                    // alert('Current view: ' + view.name);
                    // change the day's background color just for fun
                    // $(this).css('background-color', 'red');
                },
                /*navLinkDayClick: function(date, jsEvent) {
                 //                            $('#classe-planning').fullCalendar('changeView', 'agendaDay');
                 // console.log('day', date.format()); // date is a moment
                 // console.log('coords', jsEvent.pageX, jsEvent.pageY);
                 },*/
                navLinkWeekClick: function(weekStart, jsEvent) {
                    // alert('week start', weekStart.format()); // weekStart is a moment
                    // alert('coords', jsEvent.pageX, jsEvent.pageY);
                },

                //is triggered when external events ers dropped in fullcalendar
                drop: function(date, jsEvent, ui, resourceId ) {
                    // is the "remove after drop" checkbox checked?
//                            alert(date.format())
                    if ($('#drop-remove').is(':checked')) {
                        // if so, remove the element from the "Draggable Events" list
                        $(this).remove();
                    }
                },

                //Called when a valid external jQuery UI draggable, containing event data, has been dropped onto the calendar.
                eventReceive:function( event, view){
                    console.log(event.start)
//                            $('#createCourseModal').openModal()
                    /*event.title = "FORA"
                     //                            event.rendering =  "background"
                     event.color = "#ffe688"
                     event.start = new Date("2018-05-17T09:30")
                     event.end = new Date("2018-05-17T10:30")
                     //                        event.start = moment(new Date("2018-05-17T09:30"))
                     //                        event.end = moment("2018-05-17T10:30")
                     console.log("new Date()",new Date())
                     console.log("new Date()",new Date("2018-05-17T09:30"))
                     console.log("moment()",moment())
                     console.log("moment(new DAte())",moment(new Date()))*/

//                            $('#classe-planning').fullCalendar('updateEvent', event);
//                            $('#classe-planning').fullCalendar('renderEvent', event);
//                                alert(event.start)
                },

                //Triggered when dragging stops and the inner calendar event has moved to a different day/time.
                eventDrop: function( event, delta, revertFunc, jsEvent, ui, view ){
//                            console.log(event.start)
                },

                eventClick:(ev,jsEvent,view)=>{
                    // alert()
                    // this.showEventDetails(ev);
                    this.cdtEventClick(ev)
                },


                views: {
                    agendaFourDay: {
                        type: 'agenda',
                        duration: { days: 4 },
                        buttonText: '4 day',
                        dayCount: 4,
                    },
                    basic: {
                        // options apply to basicWeek and basicDay views
                    },
                    agenda: {
                        // options apply to agendaWeek and agendaDay views
                    },
                    month:{
                        // titleFormat: 'YYYY, MM, DD'
                    },
                    week: {
                        // options apply to basicWeek and agendaWeek views
                    },
                    day: {
                        // options apply to basicDay and agendaDay views
                    }
                },

//            defaultDate: '2018-05-12',
                navLinks: true, // can click day/week names to navigate views
                editable: false,
                droppable: true, // this allows things to be dropped onto the calendar
                eventLimit: true, // allow "more" link when too many events
                // businessHours: true,
                eventLimitClick: "popover",

                events:[]

            },
        }
    },
    mounted(){
        moment.locale('fr')
        this.loadDatas()
        this.initView()
    },
    computed:{
        isReady(){
            return this.selectedProfId !=0
        },
        showWeekend(j){
            return true
        },
        cdtExists(){
            return this.selectedEvent.seance.id != null
        }


    },
    methods:{
        hooveredTd(jour,h){
            var c = this.cours.find(x=>x.jour_id==jour.id && x.horaire_id == h.id)
            if(c){
                if (this.hooveredCoursId == c.id){
                    return "primary"
                }else return ""
            }
        },
        hooverCoursTd(jour,h){
            var c = this.cours.filter(x=>x.jour_id==jour.id && x.horaire_id == h.id)
            if(c.length>0)
                this.hooveredCoursId = c[0].id
        },
        isWeekend(jour){
            if(jour.is_week_end){
                return jour.is_week_end == this.showWeekends
            }
            return true
        },
        reload(){ this.loadDatas()},
        getAccordionEncorId(jour){
            return "#"+jour.nom
        },
        getParsedCours(j){
            let cours = this.cours.filter(x=>x.jour_id==j.id)
            return cours

        },
        /*   getParsedCours(j){
         var cours = []
         if(this.selectedClasseId){
         cours = j.cours.filter(x=>x.classe_id==this.selectedClasseId)
         return cours
         }
         return cours

         },*/
        initView(){
            $('#cdt-calendar').fullCalendar(this.cdt_fc_options)
        },
        createNewCours(){
            instance.post("create_cours",this.newCours).then(res=>{
                this.selectedClasse.cours.push(res.data);
                $.gritter.add({
                    // (string | mandatory) the heading of the notification
                    title: 'Nouveau cours ajouter avec success',
                    // (string | mandatory) the text inside the notification
                    class_name: 'success',
                    time: 3000,
                    position: 'top-right',
                    sticky: false
                });/*
                 if (this.selectedClasseId){
                 this.selectedClasse =
                 }*/
                // this.loadDatas()
                /*console.log(res.data)
                 if(this.selectedClasseId == res.data.classe_id){
                 this.cours.push(res.data)
                 }*/
            }).catch(err=>{
                console.log(err.response.data)
                $.gritter.add({
                    // (string | mandatory) the heading of the notification
                    title: 'Echec',
                    // (string | mandatory) the text inside the notification
                    class_name: 'danger',
                    time: 3000,
                    position: 'top-right',
                    sticky: false
                });
            })
        },
        loadDatas(){
            instance.get('load_prof_home').then(resp=>{
                console.log(resp.data)
                this.classes = resp.data.classes
                this.jours = resp.data.jours
                this.horaires = resp.data.horaires
                this.matieres = resp.data.matieres
                this.salles = resp.data.salles
                this.cours = resp.data.cours
                this.resetCalendar()
            }).catch(err=>{
                console.log(err.response.data)
            })
        },

        resetCalendar(){
            this.cdt_fc_options.events = [];
            this.cours.forEach(c=>{
                // console.log(c.jour.nom)
                this.cdt_fc_options.events.push({
                    //basic properties
                    id:c.id,
                    title:c.classe.nom,
                    // color: "#01579b",
                    color: "#01579b",
                    couleur: c.matiere.couleur,
                    textColor:"#fff",
                    // textColor:"#000",
                    start: c.horaire.debut,
                    end:c.horaire.fin,
                    allDay: false,
                    eventStartEditable: false,
                    eventDurationEditable: false,
                    dow:[c.jour.dow],
                    customRender: true,
                    //extra properties
                    classe:c.classe.nom,
                    matiere:c.matiere.intitule,
                    prof:c.prof.nom_complet,
                    salle:c.salle?c.salle.nom:"",
                    horaire: c.horaire.debut+" - "+c.horaire.fin,
                    appel:0,
                    seances:c.seances

                })
            })

            $('#cdt-calendar').fullCalendar("destroy");
            $('#cdt-calendar').fullCalendar(this.cdt_fc_options)
        },

        /*loadPlanning(){
         instance.get('load_classe_horaire/'+this.selectedClasseId)
         .then(resp=>{
         console.log(resp.data)
         this.horaires = resp.data.horaires
         })
         .catch(err=>{
         console.log(err.response.data)
         })
         },*/
        getCoursMatiere(jour,h){
            var c = this.cours.filter(x=>x.jour_id==jour.id && x.horaire_id == h.id)
            if(c.length>0)
                return c[0].matiere.intitule
            return ""
        },
        getCoursClasse(jour,h){
            var c = this.cours.filter(x=>x.jour_id==jour.id && x.horaire_id == h.id)
            if(c.length>0)
                return c[0].classe.nom
            return ""
        },
        getCoursMatiereColor(jour,h){
            // console.log("MAMA",jour)
            var c = this.cours.filter(x=>x.jour_id==jour && x.horaire_id == h)
            if(c.length>0)
            // console.log(c[0].matiere.couleur)
                return c[0].matiere.couleur3
            return ""
        },
        onProfChange(profId){
            //must
            // alert(classeId)
            this.selectedProfId = profId
            this.selectedProf = this.profs.filter(it=>it.id==profId)[0]
            this.cours = this.selectedProf.cours
            this.cdt_fc_options.events = [];
            this.cours.forEach(c=>{
                // console.log(c.jour.nom)
                this.cdt_fc_options.events.push({
                    //basic properties
                    id:c.id,
                    title:c.matiere.intitule,
                    // color: "#01579b",
                    color: "#01579b",
                    couleur: c.matiere.couleur,
                    textColor:"#fff",
                    // textColor:"#000",
                    start: c.horaire.debut,
                    end:c.horaire.fin,
                    allDay: false,
                    eventStartEditable: false,
                    eventDurationEditable: false,
                    dow:[c.jour.dow],
                    customRender: true,
                    //extra properties
                    classe:c.classe.nom,
                    matiere:c.matiere.intitule,
                    prof:c.prof.nom_complet,
                    salle:c.salle?c.salle.nom:"",
                    horaire: c.horaire.debut+" - "+c.horaire.fin,
                    appel:0,
                    seances:c.seances

                })
            })

            $('#cdt-calendar').fullCalendar("destroy");
            $('#cdt-calendar').fullCalendar(this.cdt_fc_options)
            // this.loadPlanning()
            // alert(this.selectedClasse.nom)
        },

        cdtEventClick(ev){
            this.selectedEvent = ev
            if(this.selectedEvent.seances.length>0){
                let seance = this.selectedEvent.seances.filter(s=>s.cours_id == this.selectedEvent.id && s.date == this.selectedEvent.start.format("YYYY-MM-DD"))[0]
                if(seance!=null){
                    this.selectedEvent.seance = seance
                    this.selectedSeanceId = seance.id
                }else {
                    this.selectedEvent.seance = {}
                }
            }else {
                this.selectedEvent.seance = {}
            }
            $('#cdt-create-modal').modal('show')
        },

        showCdtCreatorModal(ev){

        },

        showCdtUpdatorModal(ev){

        },

        showCoursUpdatorModal(jour,h){
            // this.selectedCours = this.cours.filter(x=>x.jour_id==jour.id && x.horaire_id == h.id)[0]
            this.currentHeure = h
            this.currentJour = jour
            if (this.cours.filter(x=>x.jour_id==jour.id && x.horaire_id == h.id).length!=0){
                this.selectedCours = this.cours.find(x=>x.jour_id==jour.id && x.horaire_id == h.id)
                $('#update-cours-modal').modal('show')
            }else {
                // $('#create-cours-modal').modal('show')
            }

        },
        updateCours(){

        },
        deleteCours(){

        },
        createSeance(){
            var cdt = {
                cours_id:this.selectedEvent.id,
                date: this.selectedEvent.start.format("YYYY-MM-DD"),
                titre:this.selectedEvent.titre,
                contenu:this.selectedEvent.contenu
            }
            instance.post('store_cdt',cdt).then(res=>{
                console.log(res.data)
                $.gritter.add({
                    // (string | mandatory) the heading of the notification
                    title: 'Cahier de texte enrégistrer avec success',
                    // (string | mandatory) the text inside the notification
                    class_name: 'success',
                    time: 2000,
                    position: 'top-right',
                    sticky: false
                });
            }).catch(err=>{
                console.log(err.response.data)
                $.gritter.add({
                    // (string | mandatory) the heading of the notification
                    title: "Erreur d'enrégistement",
                    // (string | mandatory) the text inside the notification
                    class_name: 'error',
                    time: 3000,
                    position: 'top-right',
                    sticky: false
                });
            })
        },
        updateSeance(){},
    }
};

let vm = new Vue({
    el:"#app",
    data:{},
    components:{ home },
    mounted(){}
})