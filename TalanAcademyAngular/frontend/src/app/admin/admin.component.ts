import { Component, OnInit } from '@angular/core';
import { MenuItem, PrimeNGConfig } from 'primeng/api';
import { Router } from '@angular/router';


@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss'],
})
export class AdminComponent implements OnInit {
  itemsMenu: MenuItem[] = [];
  itemsProfil: MenuItem[] = [];
  window = window;

  constructor(private primengConfig: PrimeNGConfig, private router: Router) {}

  ngOnInit(): void {
    this.primengConfig.ripple = true;
    this.itemsMenu = [
      {
        label: 'Dashboard',
        command: () => this.profil(),
      },
      {
        label: 'Cursus',
        command: () => this.cursus(),
      },
      {
        label: 'Utilisateurs',
        command: () => this.users(),
      },
    ];
    this.itemsProfil = [
      {
        label: 'Mon dashboard',
        icon: 'my-margin-left pi pi-fw pi-user',
        command: () => this.profil(),
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
    
    
  }
  logout() {
    localStorage.clear()
    this.router.navigate(['']);
  }
  profil() {
    this.router.navigate(['admin']);
  }
  home() {
    this.router.navigate(['']);
  }

  users() {
    this.router.navigate(['admin/utilisateurs']);
  }
  cursus(){
    this.router.navigate(['admin/cursus'])
  }
}
