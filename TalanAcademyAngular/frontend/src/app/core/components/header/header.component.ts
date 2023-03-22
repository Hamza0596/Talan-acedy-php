import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, ActivationEnd, Router } from '@angular/router';
import { co } from '@fullcalendar/core/internal-common';
import { MenuItem } from 'primeng/api';
import { DialogService } from 'primeng/dynamicdialog';
import { LoginComponent } from 'src/app/core/components/login/login.component';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
  providers: [DialogService]
})
export class HeaderComponent implements OnInit {
  items!: MenuItem[];
  display :boolean = true;
  constructor(public dialogService: DialogService, private route: ActivatedRoute,private router : Router) {};
  user:any=null;
  role : any;
  itemsProfil: MenuItem[] = [];
  itemsProfilDisplay : boolean = false

  ngOnInit(): void {
    if (localStorage.getItem('user_data')!=undefined) {
      this.user=localStorage.getItem('user_data')
      this.role=JSON.parse(this.user).roles[0]
      this.itemsProfilDisplay=true
      
      if (this.role=="ROLE_APPRENTI") {
        this.items = [
       
          {
              label: 'CURSUS',
          },
          {
              label: 'COMMUNAUTÉ',
          },
          {
              label: 'QUI SOMMES-NOUS?',
          },
          
        ];
        this.itemsProfil = [
          {
            label: 'Mon Dashboard',
            icon: 'my-margin-left pi pi-fw pi-user',
            command: () => this.dashboardApprentis(),
          },
          {
            label: 'Mon profil',
            icon: 'my-margin-left pi pi-fw pi-user',
            command: () => this.profileApprentis(),
          },
          {
            separator: true,
          },
          {
            label: 'Déconnexion',
            icon: 'my-margin-left pi pi-fw pi-sign-out',
            command: () => this.logout(),
          },
        ];
        
        
      }else  {
        this.items = [
          
          {
              label: 'CURSUS',
          },
          {
              label: 'COMMUNAUTÉ',
          },
          {
              label: 'QUI SOMMES-NOUS?',
          },
          
        ];
        this.itemsProfil = [
          {
            label: 'Mon dashboard',
            icon: 'my-margin-left pi pi-fw pi-user',
            command: () => this.profileAdmin(),
          },
          {
            separator: true,
          },
          {
            label: 'Déconnexion',
            icon: 'my-margin-left pi pi-fw pi-sign-out',
            command: () => this.logout(),
          },]
        
      }
      
      
    }
     else{
      this.items = [
     
        {
            label: 'CURSUS',
        },
        {
            label: 'COMMUNAUTÉ',
        },
        {
            label: 'QUI SOMMES-NOUS?',
        },
        {
            label: 'CONNEXION',
            command: () => {
              if (this.display) {
                this.login();
                this.display=false
              } 
            }
        },
        {
            label: 'INSCRIPTION',
            // icon: 'pi pi-fw pi-pencil',
        }
      ];

    }
    
    
  }

  login(returnUrl?: any) {
    if (this.display) {
      this.dialogService.open(LoginComponent, {
        // header: 'Sign In',
        // width: '40%',
        data: returnUrl,
        contentStyle: { "min-width": window.innerWidth < 800 ? "80vw" : "40vw", "overflow": "auto" },
        dismissableMask: true,
        showHeader: false,
        baseZIndex: 3,
        closable:true,
      }).onClose.subscribe(()=>{
        this.display=true
      });
    }
  }
  home(){
    this.router.navigateByUrl('/')
  }

  profileAdmin(){
    this.router.navigateByUrl('/admin')

  }
  profileApprentis(){
    this.router.navigateByUrl('/apprenti/dashboard')
  }
  dashboardApprentis(){
    this.router.navigateByUrl('/apprenti')
  }
  logout() {
    localStorage.clear();
    this.itemsProfilDisplay=false
    console.log(this.user);
    this.items = [

      {
        label: 'CURSUS',
      },
      {
        label: 'COMMUNAUTÉ',
      },
      {
        label: 'QUI SOMMES-NOUS?',
      },
      {
        label: 'CONNEXION',
        command: () => {
          if (this.display) {
            this.login();
            this.display=false
          } 
        }
      },
      {
        label: 'INSCRIPTION',
        // icon: 'pi pi-fw pi-pencil',
      }
    ];
    
    
  }
}
