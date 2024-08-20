# from predict import statistics
import random
import os

def main():
    angelType, monsterType = compare(statistics)
    choose(angelType, monsterType)

# 找出正負面代表情緒
def compare(statistics): 
    # statistics = [46,15,89,23,78,56]
    angel = ['like', 'happiness']
    monster = ['sadness', 'disgust', 'anger']
    pos = [statistics[2], statistics[4]]
    neg = [statistics[0], statistics[1], statistics[3]]

    if (statistics[2] == statistics[4]!= 0):#正面情緒相等且其一不為0
        angelType = random.choice(angel)
    elif (statistics[2]!= 0 or statistics[4] != 0): #正面情緒其一不為0
        angelType = angel[pos.index(max(pos))]
    else :
        angelType = "other"
    # print(angelType)

    if (statistics[0] == statistics[1] == statistics[3] != 0):#負面情緒相等且其一不為0
        monsterType= random.choice(monster)
    elif (statistics[0]!= 0 or statistics[1] != 0 or statistics[3]!= 0): #負面情緒其一不為0
        monsterType= monster[neg.index(max(neg))]
    else :
        monsterType= "other"
    # print(monsterType)
    return (angelType, monsterType)


def choose(angelType, monsterType):
    # source = "C:/xampp/htdocs/smiley_backend/img/angel_monster"
    num1 = random.randint(1, 6) 
    num2 = random.randint(1, 6) 
    angel = f'{angelType}_{num1}.png'     
    monster = f'{monsterType}_{num2}.png'     
    if (angelType == 'other' and monsterType == 'other'):
        while (num1 == num2):
            num2 = random.randint(1, 6) 
    monster = f'{monsterType}_{num2}.png' # 確保同時是other也不會是同一張
        
    return(angel, monster)


if __name__ == "__main__":
    main()