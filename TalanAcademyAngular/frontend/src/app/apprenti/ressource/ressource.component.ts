import { Component, OnInit } from '@angular/core';
import { RessourceService } from '../service/ressource.service';
import { Ressource } from '../models/ressource';
import { MessageService } from 'primeng/api';
import { Router } from '@angular/router';

@Component({
  selector: 'app-ressource',
  templateUrl: './ressource.component.html',
  styleUrls: ['./ressource.component.scss'],
})
export class RessourceComponent implements OnInit {
  ressources!: Ressource[];
  title: any;
  searchText: any;
  url: any;
  loading: boolean = true;
  activityValues: number[] = [0, 50];

  constructor(
    private ressourceService: RessourceService,
    private router: Router,
    private messageService: MessageService
  ) {}

  ngOnInit() {
    this.ressourceService.getRessource().subscribe(
      (resp) => {
        this.ressources = resp.resources;
        console.log(this.ressources)
      this.loading = false;
      },
      (error) => {
        if (error != null) {
          if (error.status === 400) {
            this.loading = false;
            this.messageService.add({
              severity: 'info',
              summary: 'Informations',
              detail: error.error.message,
            });
          }
        }
      }
    );
  }

  getUrl(url: string) {
    window.open(url, '_blank');
  }
}