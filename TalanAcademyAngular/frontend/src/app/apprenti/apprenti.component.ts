import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { Router } from '@angular/router';
import { MenuItem, PrimeNGConfig } from 'primeng/api';
import { ApprentiService } from '../shared/services/apprenti.service';

@Component({
  selector: 'app-apprenti',
  templateUrl: './apprenti.component.html',
  styleUrls: ['./apprenti.component.scss'],
})
export class ApprentiComponent implements OnInit {
  itemsMenu: MenuItem[] = [];
  itemsMenu1: MenuItem[] = [];
  itemsProfil: MenuItem[] = [];
  window = window;
  selectedSyno: any;
  iconStatus: any;
  constructor(
    public router: Router,
    private apprentiService: ApprentiService,
    private primengConfig: PrimeNGConfig,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.primengConfig.ripple = true;
    this.itemsMenu = [
      {
        label: 'Dashboard',
        command: () => this.dashboard(),
      },
      {
        label: 'Cursus',
        command: () => this.cursus(),
      },
    ];
    this.itemsMenu1 = [];
    let key: number = 0;
    let key1: number = 0;
    this.apprentiService.getCurrentLesson().subscribe((resp) => {
      this.apprentiService.backResponse$.next(resp);
      const menu: any = [
        {
          label: 'Dashboard',
          command: () => this.dashboard(),
        },
        {
          label: 'Cursus',
          icon: 'pi pi-chevron-down',
          command: () => this.cursus(),
        },
      ];
      let indexLesson = 0;
      resp.listModules.forEach((element: any) => {
        const branch: any = {};
        key = key + 1;
        branch.key = key;
        branch.label = branch.key.toString().concat('-').concat(element.title);
        branch.items = [];
        key1 = 0;
        element.DayCourses.forEach((course: any) => {
          let courseData = {
            indexLesson: indexLesson,
            key: key,
            key1: key1 + 1,
            title: course.description,
            synopsis: course.synopsis,
            Resources: course.ressources,
            ActivityCourses: course.activities,
          };
          if (course.status == 'jour-validant') {
            this.iconStatus = 'pi pi-calendar-times';
          } else if (course.status == 'jour-correction') {
            this.iconStatus = 'pi pi-check-circle';
          } else {
            this.iconStatus = '';
          }
          key1 = key1 + 1;
          branch.items.push({
            key1: key1,
            label: branch.key
              .toString()
              .concat('.')
              .concat(key1)
              .concat(' ')
              .concat(course.description),
            command: () => this.selectCourse(courseData),
          });
          indexLesson += 1;         
        });
        menu.push(branch);
      });
      this.itemsMenu1 = menu;
    });

    this.itemsProfil = [
      {
        label: 'Mon profil',
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
    localStorage.clear();
    this.router.navigate(['']);
  }
  profil() {
    this.router.navigate(['apprenti/dashboard']);
  }
  dashboard() {
    this.router.navigate(['apprenti']);
  }
  cursus() {
    this.router.navigate(['apprenti/course']);
  }

  home() {
    this.router.navigate(['/']);
  }
  ressource() {
    this.router.navigate(['apprenti/ressource']);
  }
  selectCourse(courseData: any) {
    this.selectedSyno = courseData.synopsis;
    this.apprentiService.synopsis$.next(courseData);
    this.router.navigate(['apprenti/course']);
    this.cdr.detectChanges();
  }
}
